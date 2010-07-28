<?php
/**

Copyright (c) 2009, SilverStripe Australia PTY LTD - www.silverstripe.com.au
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the 
      documentation and/or other materials provided with the distribution.
    * Neither the name of SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software 
      without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY 
OF SUCH DAMAGE.

*/

/**
 * An example external content connector that lists content from the filesystem
 * 
 * Please note: Set a base_path variable to prevent users entering any
 * arbitrary path!
 * 
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 *
 */
class FileSystemContentSource extends ExternalContentSource
{
	public static $db = array(
		'FolderPath' => 'Varchar(255)',
	);
	
	/**
	 * Please set this variable! It is prepended to the 
	 * FolderPath variable whenever it is used. 
	 * 
	 * @var unknown_type
	 */
	public static $base_path = '';
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Main', new TextField('FolderPath', _t('FileSystemContentSource.FOLDER_PATH', 'Folder Path')));
		return $fields;
	}
	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		
		$this->FolderPath = str_replace('\\', '/', $this->FolderPath);
	}
	
	/**
	 * Get the file path including its base_path
	 * 
	 * @return String
	 */
	public function getFilePath()
	{
		$trimmedPath = rtrim($this->FolderPath, '/');
		
		$prefix = strlen(self::$base_path) ? rtrim(self::$base_path, '/') : ($trimmedPath{0} == '/' ? '' : Director::baseFolder());
		$prefix = strlen($prefix) ? $prefix . '/' : $prefix;
		
		$path = realpath($prefix . $trimmedPath);

		return str_replace('\\', '/', $path);
	}

	/**
	 * Get a filesystem object
	 *
	 * @param String $id
	 *			The encoded ID of the item
	 * @return FileSystemContentItem
	 */
	public function getObject($id)
	{
		
		$remoteId = $this->decodeId($id);
		// TODO: Add safety things in here to make sure that our filepath isn't
		// going to list out things like /important/secrets!
		// windows realpath returns with \ path characters
		$filePath = str_replace('\\', '/', realpath($this->getFilePath().$remoteId));
		
		// make sure it's still in the filepath
		if (strpos($filePath, $this->getFilePath()) !== 0) {
			singleton('ECUtils')->log("$filePath is not part of ".$this->getFilePath());
			return null;
		}

		if (!file_exists($filePath)) {
			return null; 
		}

		$item = new FileSystemContentItem($this, $remoteId);
		
		return $item;
	}

	public function getRoot()
	{
		return $this->getObject('/');
	}

	public function stageChildren()
	{
		$root = $this->getRoot();
		return $root ? $root->stageChildren() : null;
	}
	
	public function allowedImportTargets()
	{
		return array('file' => true);
	}
	
	public function getContentImporter($target=null)
	{
		return new FileSystemContentImporter();
	}

	public function getType()
	{
		$root = $this->getRoot();
		if ($root) {
			return $root->getType();
		}
	}
}

?>
