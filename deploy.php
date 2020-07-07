<?php

namespace Deployer;

require 'recipe/laravel.php';
require 'recipe/yarn.php';

// Project name
set('application', 'simple-weather');

// Project repository
set('repository', 'https://github.com/Pr3d4dor/simple-weather.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

set('ssh_multiplexing', true); // Speed up deployment

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);

// Hosts
host('bine-server') // Name of the server
    ->hostname('34.67.170.22') // Hostname or IP address
    ->stage('production') // Deployment stage (production, staging, etc)
    ->user('gbine') // SSH user
    ->set('deploy_path', '~/{{application}}'); // Deploy path

// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

desc('Build Yarn Assets');
task('yarn:build-assets', function () {
    run("cd {{release_path}} && {{bin/yarn}} prod");
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');

desc('Deploy the application');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'yarn:install',
    'yarn:build-assets',
    'artisan:storage:link', // |
    'artisan:view:cache',   // |
    'artisan:config:cache', // | Laravel specific steps
    'artisan:optimize',     // |
    'artisan:migrate',      // |
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);
