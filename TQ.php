use TQ\Git\Repository\Repository;
// open an already initialized repository
$git = Repository::open('/path/to/your/repository', '/usr/bin/git');

// open repository and create path and init repository if necessary
$git = Repository::open('/path/to/your/repository', '/usr/bin/git', 0755);

// get current branch
$branch = $git->getCurrentBranch();

// get status of working directory
$status = $git->getStatus();
// are there uncommitted changes in the staging area or in the working directory
$isDirty = $git->isDirty();

// retrieve the commit log limited to $limit entries skipping the first $skip
$log = $git->getLog($limit, $skip);

// retrieve the second to last commit
$commit = $git->showCommit('HEAD^');

// list the directory contents two commits before
$list  = $git->listDirectory('.', 'HEAD^^');

// show contents of file $file at commit abcd123...
$contents = $git->showFile($file, 'abcd123');

// write a file and commit the changes
$commit = $git->writeFile('test.txt', 'Test', 'Added test.txt');

// remove multiple files
$commit = $git->removeFile('file_*', 'Removed all files not needed any more');

// rename a file
$commit = $c->renameFile('test.txt', 'test.txt-old', 'Made a backup copy');

// do some file operations and commit all changes at once
$result = $git->transactional(function(TQ\Vcs\Repository\Transaction $t) {
    file_put_contents($t->getRepositoryPath().'/text1.txt', 'Test 1');
    file_put_contents($t->getRepositoryPath().'/text2.txt', 'Test 2');

    unlink($t->resolvePath('old.txt'));
    rename($t->resolvePath('to_keep.txt'), $t->resolvePath('test3.txt'));

    $t->setCommitMsg('Don\'t know what to write here');

    // if we throw an exception from within the callback the changes are discarded
    // throw new Exception('No we don\'t want to to these changes');
    // note: the exception will be re-thrown by the repository so you have to catch
    // the exception yourself outside the transactional scope.
});
Using the streamwrapper
use TQ\Git\StreamWrapper\StreamWrapper;

// register the wrapper
StreamWrapper::register('git', '/usr/bin/git');

// read the contents of a file
$content = file_get_contents('git:///path/to/your/repository/file_0.txt');

// show contents of a file at commit abcd123...
$content = file_get_contents('git:///path/to/your/repository/file_0.txt#abcd123');

// show contents of a file two commits before
$content = file_get_contents('git:///path/to/your/repository/file_0.txt#HEAD^^');

// show the directory information two commits before
$directory = file_get_contents('git:///path/to/your/repository/#HEAD^^');

// list directory contents two commits before
$dir = opendir('git:///path/to/your/repository/subdir#HEAD^^');
while ($f = readdir($dir)) {
    echo $f.PHP_EOL;
}
closedir($dir);

// recursively traverse the repository two commits before
$dir = new RecursiveDirectoryIterator('git:///path/to/your/repository#HEAD^^');
$it  = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);
foreach ($it as $fileInfo) {
    echo str_repeat(' ', $it->getDepth() * 3).$fileInfo->getFilename().PHP_EOL;
}

// retrieve the second to last commit
$commit = file_get_contents('git:///path/to/your/repository?commit&ref=HEAD^^');

// retrieve the commit log limited to 5entries skipping the first 2
$log = file_get_contents('git:///path/to/your/repository?log&limit=5&skip=2');

// remove a file - change is committed to the repository
unlink('git:///path/to/your/repository/file_to_delete.txt');

// rename a file - change is committed to the repository
rename('git:///path/to/your/repository/old.txt', 'git:///path/to/your/repository/new.txt');

// remove a directory - change is committed to the repository
rmdir('git:///path/to/your/repository/directory_to_delete');

// create a directory - change is committed to the repository
// this creates a .gitkeep file in new_directory because Git does not track directories
mkdir('git:///path/to/your/repository/new_directory');

// write to a file - change is committed to the repository when file is closed
$file = fopen('git:///path/to/your/repository/test.txt', 'w');
fwrite($file, 'Test');
fclose($file);

// support for stream context
$context = stream_context_create(array(
    'git'   => array(
        'commitMsg' => 'Hello World',
        'author'    => 'Luke Skywalker <skywalker@deathstar.com>'
    )
));
$file = fopen('git:///path/to/your/repository/test.txt', 'w', false, $context);
fwrite($file, 'Test');
fclose($file); // file gets committed with the preset commit message and author

// append to a file using file_put_contents using a custom author and commit message
$context = stream_context_create(array(
    'git'   => array(
        'commitMsg' => 'Hello World',
        'author'    => 'Luke Skywalker <skywalker@deathstar.com>'
    )
));
file_put_contents('git:///path/to/your/repository/test.txt', 'Test', FILE_APPEND, $context);

// it is now possible to register repository-specific paths on the stream wrapper
StreamWrapper::getRepositoryRegistry()->addRepositories(
    array(
        'repo1' => Repository::open('/path/to/repository/1', '/usr/bin/git', false),
        'repo2' => Repository::open('/path/to/repository/2', '/usr/bin/git', false),
    )
);
$content1 = file_get_contents('git://repo1/file_0.txt');
$content2 = file_get_contents('git://repo2/file_0.txt');

// unregister the wrapper if needed
StreamWrapper::unregister();
