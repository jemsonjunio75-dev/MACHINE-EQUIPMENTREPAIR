<?php
date_default_timezone_set('Asia/Manila'); // Set timezone
$servername = "localhost";
$username = "root";
$password = base64_decode("Y29tcHJvZzE=");
$dbname = "machine_equipment_repair";
$port = 3307;

// Set filename with date
$backup_file = 'backup_MACHINEREPAIR' . date("Y-m-d_H-i-s") . '.sql';
$backup_path = 'C:\\Users\\jeric\\OneDrive\\Desktop\\BACKUP-DATABASES\\MACHINE REPAIR RECORD\\' . $backup_file;

// Ensure backup directory exists
if (!file_exists(dirname($backup_path))) {
    mkdir(dirname($backup_path), 0777, true);
}

// MySQL Server's mysqldump path (not XAMPP)
$mysqldumpPath = '"C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe"';

// Build full command with routines, events, and custom port
$command = "$mysqldumpPath --routines --events --user=$username --password=$password --host=$servername --port=$port $dbname > \"$backup_path\"";

// Execute command
system($command, $output);

// Check if backup was successful
if ($output === 0) {
    $backupFilePath = "C:/Users/jeric/OneDrive/Desktop/BACKUP-DATABASES/MACHINE REPAIR RECORD/";

    echo "Backup Success!";
    
} else {
    echo "❌ Backup failed!";
}
?>
