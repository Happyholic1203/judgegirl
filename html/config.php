<?php
# First Section of PHP Variables:
#   Only content of this section will be loaded by Perl Script
#
#   Inorder to cooperate with Perl, only '#' comment style could be used 
#   in this file, and make sure contents here is readable to Perl.

# Course Informations
$StrCourse = "103-1 計算機程式";
$StrCourseName = $StrCourse." 線上批改系統";
$StrCourseNameEng = $StrCourse." JudgeGirl system";

# Site Informations
$TimeZone  = "Asia/Taipei";
$JudgeAddr = "http://192.168.56.95/";
$CourseAddr= "http://192.168.56.95/";
# $JudgeAddr = "http://example.com/~user/";
# $CourseAddr= "http://example.com/course-address/";

# DB information, these tokens should not appear twice
# or Bash script would fail
$MySQLhost = "localhost";
$MySQLuser = "1031_programming";
$MySQLpass = "0p;/9ol.8ik,";
$MySQLdatabase = "1031_programming";

# Instructor Name
$InstName = "林宗男";

# TA informations, should be separated by ", "
$TAnames   = "TA2 Two, TA1 One";
$TAemails  = "ta2@mail.com, ta1@mail.com";

# TODO: add this into install.pl
$testPathBase = "/home/1031_programming/problems/upload";
$webRoot = "/home/1031_programming/public_html";
$probDescBaseUrl = "docs";
$probDescPath = "$webRoot/$probDescBaseUrl"; # path to problem descriptions
$restartScript = "/home/1031_programming/judge/restart.sh";
?>

<?php
# PHP-only variables goes here
# User to be ignored
$BlackList = array();
$HomeworkVolumes = array('');

$QuizEnv         = 0;
$ContestEnv      = 0;
$ContestIP       = ''; # Contest Room IP
$ContestBeginTime= '';
$ProblemVolume   = "";
$ProblemMaxScore = 10;
$ProblemScore[0] = 10;
$ProblemScore[1] = 10;
$ProblemScore[2] = 10;
$ProblemScore[3] = 10;
$ProblemScore[4] = 10;
$ProblemScore[5] = 10;
$ProblemScore[6] = 10;
$ProblemScore[7] = 10;
$ProblemScore[8] = 10;
$ProblemScore[9] = 10;
$ProblemScore[10] = 10;
$ProblemScore[11] = 10;
$ProblemScore[12] = 10;
$ProblemScore[13] = 10;
$ProblemScore[14] = 10;
$ProblemScore[15] = 10;
$ProblemScoreSize = 16;
$ProblemColor[0] = "ddeeff";
$ProblemColor[1] = "ffeeee";
$ProblemColor[2] = "ffffee";
$ProblemColor[3] = "eeffdd";
$ProblemColor[4] = "eeffff";
$ProblemColor[5] = "eeeeff";
$ProblemColor[6] = "ddddff";
$ProblemColor[7] = "eedddd";
$ProblemColor[8] = "ddeedd";
$ProblemColor[9] = "dddddd";
$ProblemColor[10] = "ddeeff";
$ProblemColor[11] = "eeddcc";
$ProblemColor[12] = "cceedd";
$ProblemColor[13] = "ffddee";
$ProblemColor[14] = "ddeecc";
$ProblemColor[15] = "ffddee";
$ProblemColorSize = 16;
?>
