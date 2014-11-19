<?php
require_once("config.php");

session_start();

if(!$_SESSION["SU"])
    exit("Permission denied.");

if ($_FILES['file']['error'] > 0) {
    echo 'Error code: '. $_FILES['file']['error']. '<br/>';
}
else {
    $adder = new VolumeAdder($_FILES['file']);
    if (!$adder->connectDB($MySQLhost, $MySQLdatabase, $MySQLuser, $MySQLpass))
        exit("Failed to connect to database: $MySQLdatabase<br/>");
    $adder->setProbDescBaseUrl($probDescBaseUrl);
    $adder->setTestPathBase($testPathBase);
    $adder->setProbDescPath($probDescPath);
    $adder->check();
    $adder->extractVolume();
    $adder->addVolume();
    $adder->close();
}

class VolumeAdder {
    private static $upload_dir = 'upload';
    private $extractedFile = '';
    private $fileBaseName = '';
    private $curDir = '';
    private $testPathBase = '';
    private $probDescPath = '';
    private $pdo = null;
    private $probDescBaseUrl = '';

    public function __construct($file) {
        $this->file = $file;
    }

    public function connectDB($host, $dbName, $user, $passwd) {
        $this->pdo = new PDO("mysql:host=$host;dbname=$dbName", $user, $passwd);
        if (!$this->pdo)
            return false;
        return true;
    }

    public function check() {
        if ($this->file['error'] > 0)
            exit('File error code: '. $this->file['error']);
        if (!is_writable($this::$upload_dir))
            exit("`". $this::$upload_dir. "` is not writable");
    }

    public function extractVolume() {
        $target = $this::$upload_dir. '/'. $this->file['name'];
        $this->fileBaseName = basename($this->file['name'], '.zip');
        move_uploaded_file($this->file['tmp_name'], $target);

        $zip = new ZipArchive;
        $res = $zip->open($target);
        $name = $this->file['name'];
        $upload_dir = $this::$upload_dir;
        if ($res === TRUE) {
            $this->extractedFile = "$upload_dir/". md5($name. date());
            $zip->extractTo($this->extractedFile);
            $zip->close();
            echo "$name extracted<br/>";
            unlink($target);
            echo "$name deleted<br/>";
        }
        else {
            exit("Invalid zip file: $name");
        }
    }

    public function addVolume() {
        $volumeName = $this->fileBaseName;
        $volumeTitle = $this->fileBaseName;
        $volumeType = 'CPP';
        $this->_addVolume2DB($volumeName, $volumeTitle, $volumeType, 0);
        foreach($this->_getProblems() as $p) {
            chdir($p);
            $testPath = $this->testPathBase. '/'. $this->fileBaseName. '/'. $p;
            $this->_cpDir(
                '*.{in,out}',
                $testPath);

            $this->_cpDir(
                '*.html',
                $this->probDescPath. '/'. $this->fileBaseName);

            $probDescUrl = $this->probDescBaseUrl. '/'. $this->fileBaseName. "/$p.html";
            $this->_addProblem2DB($volumeName, $p, $probDescUrl, $testPath);
            chdir('..');
        }
    }

    public function close() {
        unlink($this->extractedFile);
        echo $this->extractedFile. ' deleted<br/>';
    }
    
    public function setProbDescBaseUrl($baseUrl) {
        $this->probDescBaseUrl = $baseUrl;
    }

    public function setProbDescPath($base) {
        $this->probDescPath = $base;
    }

    public function setTestPathBase($base) {
        $this->testPathBase = $base;
    }

    private function _getProblems() {
        chdir($this->extractedFile. '/'. $this->fileBaseName);
        $cmd = 'ls';
        preg_match_all('/(.+)/', shell_exec($cmd), $matches);
        return $matches[0];
    }

    private function _cp($src, $dst) {
        if (file_exists($dst))
            echo "<strong>$src -> $dst</strong><br/>";
        else
            echo "$src -> $dst<br/>";
        return copy($src, $dst);
    }

    private function _cpDir($regex, $dst) {
        if (!file_exists($dst)) {
            echo "mkdir: $dst<br/>";
            mkdir($dst, 0755, true);
        }
        if (!is_writable($dst))
            exit("`$dst` is not writable");
        foreach(glob($regex, GLOB_BRACE) as $src) {
            $this->_cp($src, $dst. '/'. basename($src));
        }
    }

    private function _addVolume2DB($name, $title, $type, $available=0) {
        if (!$this->pdo)
            exit('Not connected to database');
        $q = $this->pdo->prepare('SELECT COUNT(*) FROM volumes WHERE name = :name AND type = :type');
        $q->execute(array(
            ':name' => $name,
            ':type' => $type
        ));
        if ($q->fetchColumn() > 0)
            echo "There is already an existing volume named `$name`<br/>";
        else {
            $q = $this->pdo->prepare("INSERT INTO volumes (type, name, title, available) VALUES (?, ?, ?, ?)");
            $q->execute(array(
                $type,
                $name,
                $title,
                $available
            ));
            if ($q->errorCode() != 0) {
                print_r($q->errorInfo());
                exit();
            }
        }

        $q = $this->pdo->prepare("SELECT 1 FROM $name");
        $q->execute();
        if ($q->fetchColumn() > 0) {
            echo "Table `$name` eixsts<br/>";
        }
        else {
            $q = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS $name (
                user CHAR(16),
                program blob,
                number INTEGER UNSIGNED DEFAULT 0,
                time DATETIME,
                trial INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                score DOUBLE,
                exec_time DOUBLE,
                exec_space DOUBLE,
                log TEXT,
                exec_md5 CHAR(32),
                ip CHAR(39),
                valid TINYINT(1),
                comment TEXT,
                result VARCHAR(255),
                UNIQUE INDEX `unique_user_number_trial` (user, number, trial)
            )");
            $q->execute();
            if ($q->errorCode() != 0) {
                print_r($q->errorInfo());
                exit();
            }
        }
    }

    private function _addProblem2DB($volumeName, $problemTitle, $descUrl, $testPath, $available=0, $deadline=null) {
        // TODO: default deadline is two weeks later
        // TODO: default available = 0
        $q = $this->pdo->prepare('SELECT COUNT(*) FROM problems WHERE volume = :volume AND title = :title');
        $q->execute(array(
            ':volume' => $volumeName,
            ':title' => $problemTitle
        ));
        $this->_checkErr($q);
        if ($q->fetchColumn() > 0)
            return $this->_updateProblem($volumeName, $problemTitle, $descUrl, $testPath, $available, $deadline);

        $q = $this->pdo->prepare('INSERT INTO problems (
            volume, title, available, deadline, file, url, testpath)
            VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $dateFormat = 'Y-m-d H:i:s';
        echo 'Inserting into problems<br/>';
        $q->execute(array(
            $volumeName,
            $problemTitle,
            $available,
            $deadline ? $deadline : date($dateFormat, strtotime(date($dateFormat). " +14 day")),
            'source.cpp',
            $descUrl,
            $testPath
        ));
        $this->_checkErr($q);
    }

    private function _checkErr($q) {
        if ($q->errorCode() != 0) {
            print_r($q->errorInfo());
            exit();
        }
    }

    private function _updateProblem($volumeName, $problemTitle, $descUrl, $testPath, $available=0, $deadline=null) {
        echo "Updating existing problem: ($volumeName, $problemTitle)<br/>";
    }
}
?>
