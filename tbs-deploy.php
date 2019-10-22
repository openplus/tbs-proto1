<?php
    /**
     * GIT DEPLOYMENT SCRIPT
     *
     * Used for automatically deploying TBS prototype via github.
     *
     */
    // The commands
    $commands = array(
        'echo $PWD',
        'whoami',
        'git status',
        'git pull',
        'drush cr',
    );
    // Run the commands for output
    $output = '';
    foreach($commands AS $command){
        // Run it
        $tmp = shell_exec($command);
        // Output
        $output .= "<span style=\"color: #6BE234;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
        $output .= htmlentities(trim($tmp)) . "\n";
    }
    // Make it pretty for manual user access
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>GIT DEPLOYMENT SCRIPT</title>
</head>
<body style="background-color: #000000; color: #FFFFFF; font-weight: bold; padding: 0 10px;">
<pre>
 ____________________________
|                            |
| Git Deployment Script v0.1 |
|        &copy; OpenPlus          |
|____________________________|

<?php echo $output; ?>
</pre>
</body>
</html>
