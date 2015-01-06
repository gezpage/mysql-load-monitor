<?php

require 'mysql_load_check_config.php';

/**
 * Object instantiation
 */
$mysql = new MySqlChecker(
    new PDO(
        "mysql:host=".DB_HOST,
        DB_USER,
        DB_PASSWORD
    )
);
$alert = new Alert(
    new Email(),
    array(
        'email' => EMAIL_ENABLED,
        'text_output' => OUTPUT_TEXT
    )
);

/**
 * Check processes
 */
if (MIN_PROCESSES_TO_WARN <= ($processes = $mysql->getProcessCount())) {

    // Raise the alarm!
    $alert->raise(
        "ALERT! There are {$processes} processes currently queued.\n\n".$mysql->getFullProcessList(),
        unserialize(EMAIL_RECIPIENTS),
        EMAIL_SUBJECT
    );
} elseif (OUTPUT_TEXT) {

    echo "MySQL process count is {$processes}, minimum processes to trigger alert is ".MIN_PROCESSES_TO_WARN.". Standing down!\n";

}

class MySqlChecker
{
    private $pdo;
    private $processes = array();

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->parseProcessList();
    }

    public function getProcessCount()
    {
        return count($this->processes);
    }

    public function getFullProcessList()
    {
        return var_export($this->processes, true);
    }

    private function parseProcessList()
    {
        if (!$pdoStatement = $this->pdo->query('SHOW PROCESSLIST', PDO::FETCH_ASSOC)) {
            throw new Exception('Error getting process list');
        }
        foreach ($pdoStatement as $process) {
            $this->processes[] = $process;
        }
    }
}

class Email
{
    private $error;

    public function send($recipient, $subject, $body)
    {
        try {
            mail($recipient, $subject, $body);
        } catch (Exception $e) {
            $this->error = $e->getMessage();

            return false;
        }

        return true;
    }

    public function getError()
    {
        return $this->error;
    }
}

class Alert
{
    private $email;
    private $shouldSendEmail = false;
    private $shouldOutputText = true;

    public function __construct(Email $email, array $options = null)
    {
        $this->email = $email;

        if (isset($options['email'])) {
            $this->shouldSendEmail = (bool) $options['email'];
        }
        if (isset($options['text_output'])) {
            $this->shouldOutputText = (bool) $options['text_output'];
        }
    }

    public function raise($message, array $recipients, $subject)
    {
        if ($this->shouldOutputText) {
            echo "[*] {$message}\n";
        }
        if ($this->shouldSendEmail) {
            foreach ($recipients as $recipient) {
                if ($this->shouldOutputText) {
                    echo "[*] - Sending email to {$recipient}\n";
                }
                $this->email->send($recipient, $subject, $message);
            }
        }
    }
}
