#!/usr/bin/perl -wU

my $SystemRoot = '/home/1031_programming/judge';
my $TimeLimit = 3;
my $SpaceLimit = 16384;
my $Judgexec     = "$SystemRoot/judgexec";
my $TestCmd     = './a.out < in > out';

sub judge {
    my $cmd = qq{ 
        set -f; ulimit -t $TimeLimit; ulimit -v $SpaceLimit;
        exec $SystemRoot/bin/time --format="%e\\\\n%M\\\\n%y\\\\n" $Judgexec $TestCmd
    };
    eval "print STDERR qq{$cmd\n}";
    eval "exec qq{$cmd}";
}

# judge();
system("set -f; ulimit -t $TimeLimit; ulimit -v $SpaceLimit; exec $SystemRoot/bin/time --format='%e\\\\n%M\\\\n%y\\\\n' $Judgexec $TestCmd");

print "hi\n";
print "Timer program returns: $!\n";
print "Timer program returns: $?\n";
