<?php

namespace pallo\library\cms\content\mapper;

/**
 * Interface for a searchable content mapper
 */
interface SearchableContentMapper extends ContentMapper {

	/**
     * Gets the search results
     * @param string $site Id of the site
     * @param string $locale Code of the current locale
     * @param string $query Full search query
     * @param string $queryTokens Full search query parsed in tokens
     * @param integer $page number of the result page (optional)
     * @param integer $pageItems number of items per page (optional)
     * @return pallo\library\cms\content\ContentResult
     * @see pallo\library\cms\content\Content
	 */
	public function searchContent($site, $locale, $query, array $queryTokens, $page = null, $pageItems = null);

}