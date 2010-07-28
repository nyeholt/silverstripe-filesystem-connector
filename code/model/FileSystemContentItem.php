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
 

class FileSystemContentItem extends ExternalContentItem
{
	public function init()
	{
		$filePath = realpath($this->source->getFilePath() . $this->externalId);

		$this->Name = basename($filePath);
		$this->FilePath = $filePath;
		$this->Title = $this->Name;
		$this->Size = filesize($filePath);
		$this->LastEdited = date('Y-m-d H:i:s', filemtime($filePath));
		$this->Created = date('Y-m-d H:i:s', filectime($filePath));
	}

	public function stageChildren()
	{
		$filePath = $this->FilePath;
		$children = new DataObjectSet();
		
		if (is_dir($filePath) && is_readable($filePath)) {
			$files = scandir($filePath);
			foreach ($files as $fname) {
				if ($fname == '.' || $fname == '..') continue;

				$id = $this->source->encodeId($this->externalId . '/' . $fname);
				$item = $this->source->getObject($id);
				if ($item) {
					$children->push($item);
				}
			}
		}

		return $children;
	}

	public function numChildren()
	{
		$children = $this->Children();
		return $children->Count();
	}
	
	public function getType()
	{
		return is_dir($this->FilePath) ? 'folder' : 'file';
	}

	public function streamContent()
	{
		$filePath = $this->FilePath;
		if (file_exists($filePath)) {
			header("Content-Disposition: atachment; filename=$this->Name");
			header("Content-Type: application/octet-stream");
			header("Content-Length: ".filesize($filePath));
			header("Pragma: no-cache");
			header("Expires: 0");
			
		    readfile($filePath);
		    exit;
		} 
	    exit("Failed streaming content");
	}
}
