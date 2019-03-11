<?php

/*{{{ v.151005.001 (0.0.2)

    Sample config file for bitbucket hooks.

    Based on 'Automated git deployment' script by Jonathan Nicoal:
    http://jonathannicol.com/blog/2013/11/19/automated-git-deployments-from-bitbucket/

    See README.md and config.sample.php

    ---
    Igor Lilliputten
    mailto: igor at lilliputten dot ru
    http://lilliputtem.ru/

    Ivan Pushkin
    mailto: iv dot pushk at gmail dot com

}}}*/

// Base tool configuration:
$CONFIG = array(
    'gitCommand'       => 'git',                   // Git command, *REQUIRED*
    'repositoriesPath' => '/home/xurelndl/public_html/test', // Folder containing all repositories, *REQUIRED*
    'log'              => true,                    // Enable logging, optional
    'logFile'          => 'bitbucket.log',         // Logging file name, optional
    'logClear'         => true,                    // clear log each time, optional
    'verbose'          => true,                    // show debug info in log, optional
    'folderMode'       => 0700,                    // creating folder mode, optional
);

// List of deployed projects:
$PROJECTS = array(
    'johnathanlsimpson/vkgy' => array( // The key is a bitbucket.org repository full name *REQUIRED*
        'production' => array(
            'deployPath'  => '/home/xurelndl/public_html/test/wherefilesgo',     // Path to deploy project, *REQUIRED*
            //'postHookCmd' => 'test',     // command to execute after deploy, optional
        ),
    ),

    /*'bitbucketUsername/repoName-N' => array( // The key is a bitbucket.org repository full name *REQUIRED*
        'branch' => array(
            'deployPath'  => '/deploy_path',     // Path to deploy project, *REQUIRED*
            'postHookCmd' => 'your_command',     // command to execute after deploy, optional
        ),
    ),*/
);
?>