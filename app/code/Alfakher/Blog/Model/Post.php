<?php

namespace Alfakher\Blog\Model;

class Post extends \Magefan\Blog\Model\Post {

	public function getMetaDescription() {

		$key = 'filtered_meta_description';
		if (!$this->hasData($key)) {
			$desc = $this->getData('meta_description');
			if (!$desc) {
				$desc = $this->getShortFilteredContent();
				$desc = str_replace(['<p>', '</p>'], [' ', ''], $desc);
			}

			$desc = strip_tags($desc);

			$desc = trim($desc);

			$this->setData($key, $desc);
		}

		return $this->getData($key);
	}

}

