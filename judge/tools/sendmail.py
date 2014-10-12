#! /usr/bin/env python
# -*- coding: utf-8 -*-

# TODO: customize this part
course = u'' # may contain Chinese characters
username = ''
password = ''

email_suffix = 'ntu.edu.tw'
SMTPserver = 'smtps.ntu.edu.tw'
sender = '%s@%s' % (username, email_suffix)

title = u'Your account for %s JudgeGirl system has been created.' % (course)

# typical values for text_subtype are plain, html, xml
text_subtype = 'plain'

import sys
import os
import re

from smtplib import SMTP_SSL as SMTP       # this invokes the secure SMTP protocol (port 465, uses SSL)
# from smtplib import SMTP                  # use this for standard SMTP protocol   (port 25, no encryption)
from email.MIMEText import MIMEText

def usage():
    print 'Send emails to users in info_<user>, where <user> is his/her student ID'
    print 'Usage: ./sendemail /path/to/info_folder'

def send(destination, subject, content):
    try:
        msg = MIMEText(content, text_subtype)
        msg['Subject'] = subject
        msg['From'] = sender # some SMTP servers will do this automatically, not all

        conn = SMTP(SMTPserver)
        conn.set_debuglevel(False)
        conn.login(username, password)
        try:
            conn.sendmail(sender, destination, msg.as_string())
        finally:
            conn.close()

    except Exception, exc:
        sys.exit("mail failed; %s" % str(exc)) # give a error message

def to_email(filename):
    return filename.split('info_')[1] + '@ntu.edu.tw'

def to_id(email):
    return email.split('@')[0]

def confirm(student_ids):
    print 'The title will be: "%s"' % title
    print 'Mail list: %r' % student_ids
    print 'Are you sure you want to proceed? (Y/n)'
    proceed = raw_input() or 'Y'
    if proceed.startswith('Y') or proceed.startswith('y'):
        return True
    else:
        return False

if __name__ == '__main__':
    dir_name = None
    if not course or not username or not password:
        print '**ERROR: Please specify the course name, (email) username, and (email) password in %s' % (__file__)
        quit()
    elif sys.argv[0] == 'python':
        if len(sys.argv) != 3:
            usage()
            quit()
        else:
            dir_name = sys.argv[2]
    elif len(sys.argv) == 2:
        dir_name = sys.argv[1]
    else:
        usage()
        quit()

    student_ids = []
    for f in os.listdir(dir_name):
        if f.startswith('info_'):
            student_ids.append(to_email(f))

    if not confirm(student_ids):
        quit()

    for student_id in student_ids:
        print 'Sending email to: %s ... ' % student_id,
        f_handle = open('info_' + to_id(student_id))
        send([student_id], title, f_handle.read())
        f_handle.close()
        print 'done!'
