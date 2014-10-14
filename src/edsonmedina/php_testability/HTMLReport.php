<?php
namespace edsonmedina\php_testability;

use edsonmedina\php_testability\ReportInterface;
use edsonmedina\php_testability\ReportDataInterface;

class HTMLReport implements ReportInterface
{
	private $baseDir   = '';
	private $reportDir = '';
	private $data;

	/**
	 * @param string              $baseDir   Where the code resides
	 * @param string              $reportDir Where to generate the report 
	 * @param ReportDataInterface $data      Report data
	 */
	public function __construct ($baseDir, $reportDir, ReportDataInterface $data)
	{
		$this->baseDir   = $baseDir;
		$this->reportDir = $reportDir;
		$this->data      = $data;
	}

	/**
	 * Generate HTML report
	 */
	public function generate ()
	{
		echo "\n\nGenerating report... ";

		if (!is_dir($this->reportDir)) {
			mkdir ($this->reportDir);	
		}

		foreach ($this->data->getFileList() as $file) {
			$this->generateFile ($file);
		}

		foreach ($this->data->getDirList() as $path) {
			$this->generateIndexFile ($path);
		}

		// DEBUG
		file_put_contents ('debug.log', json_encode ($this->data->dumpAllIssues(), JSON_PRETTY_PRINT));

		echo "OK.\n\n";
	}

	/**
	 * Generate file
	 * @param string $filename
	 */
	public function generateFile ($filename)
	{
		// Load code and line numbers into array 
		$content = file ($filename);
		$code    = array (array ());

		for ($i = 1, $len = count ($content); $i <= $len; $i++) 
		{
			@$code[$i]['line'] = str_pad($i, 6, ' ', STR_PAD_LEFT).".";
			@$code[$i]['code'] = rtrim($content[$i-1]);
		}
		$content = null;


		// get issues per scope / line
		$issues = $this->data->getIssuesForFile ($filename);

		$scopes = array ();
		
		if (isset($issues['scoped'])) 
		{
			foreach ($issues['scoped'] as $scope => $report)
			{
				// count issues inside scope
				$numIssues = 0;
				foreach ($report as $type => $list) 
				{
					$numIssues += (count($list));

					// list issues per line
					foreach ($list as $issue) 
					{
						list ($name, $lineNum) = $issue;
						@$code[$lineNum]['issues'][] = array ('type' => $type, 'name' => $name);
					}
				}

				$scopes[] = array (
					'name'   => $scope . '()', 
					'issues' => $numIssues
				);
			}
		}

		if (isset($issues['global'])) 
		{
			// add global issues
			$count = 0;
			foreach ($issues['global'] as $type => $list) 
			{
				foreach ($list as $lineNum => $unused) 
				{
						@$code[$lineNum]['issues'][] = array ('type' => $type);
						$count++;
				}
			}

			$scopes[] = array (
				'name'   => '<global>',
				'issues' => $count
			);
		}

		$issues = null;

		// render
		$m = new \Mustache_Engine (array(
			'loader' => new \Mustache_Loader_FilesystemLoader (__DIR__.'/views'),
		));

		$relFilename = $this->convertPathToRelative ($filename);

		$output = $m->render ('file', array (
			'currentPath' => $relFilename,
			'scopes'      => $scopes,
			'lines'       => $code,
			'date'        => date('r')
			// 'untestable'  => $issues['global']
		));

		$this->saveFile ($relFilename.'.html', $output);
	}

	/**
	 * Generate index file
	 * @param string $path
	 */
	public function generateIndexFile ($path)
	{
		// list directory
		$files = array ();
		$dirs  = array ();
		
		foreach (new \DirectoryIterator($path) as $fileInfo) 
		{
    		if ($fileInfo->isDot()) {
    			continue;
    		} 
    		
    		$pathname = $fileInfo->getPathname();
			$filename = $fileInfo->getFilename();

    		if ($fileInfo->isDir()) 
    		{
    			$dirs[] = array (
    				'name'   => $filename,
    				'issues' => '??'//$this->data->getIssuesForDir ($pathname)
    			);
    		} 
    		elseif ($fileInfo->getExtension() == 'php')
    		{
    			$files[] = array (
    				'file'   => $filename,
    				'issues' => $this->data->getIssuesCountForFile ($pathname)
    			);
    		}
		}

		// render
		$m = new \Mustache_Engine (array(
			'loader' => new \Mustache_Loader_FilesystemLoader (__DIR__.'/views'),
		));

		$relPath = $this->convertPathToRelative ($path);

		$output = $m->render ('dir', array (
			'currentPath' => $relPath,
			'files'       => $files,
			'dirs'        => $dirs,
			'date'        => date('r')
		));

		$this->saveFile ($relPath.'/index.html', $output);		
	}

	/**
	 * Saves file to filesystem
	 * @param string $filename RELATIVE filename
	 * @param string $contents
	 */
	public function saveFile ($filename, $contents)
	{
		// make sure the directory exists
		$dirname = $this->reportDir.'/'.dirname ($filename);

		if ($dirname && !is_dir($dirname)) {
			mkdir ($dirname, 0777, true);
		}

		// save
		file_put_contents ($this->reportDir.'/'.$filename, $contents);
	}

	/**
	 * Convert absolute path into relative
	 * @param string $path
	 * @return string $path
	 */
	public function convertPathToRelative ($path)
	{
		return substr ($path, strlen($this->baseDir)+1);
	}
}