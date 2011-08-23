<?php
/**
 * @package framework.indexer
 */
interface indexer_IndexableDocument
{
	/**
	 * Get the indexable document
	 *
	 * @return indexer_IndexedDocument
	 */
	public function getIndexedDocument();
}


class indexer_BackofficeIndexedDocument extends indexer_IndexedDocument
{

}