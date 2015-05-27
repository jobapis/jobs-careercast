<?php namespace JobBrander\Jobs\Client\Providers;

use JobBrander\Jobs\Client\Job;

class Craigslist extends AbstractProvider
{
    /**
     * Rss format string for query to Craigslist
     *
     * @var string
     */
    protected $rssFormat;

    /**
     * Craigslist uses offset instead of paging
     *
     * @var string
     */
    protected $offset;

    /**
     * Returns the standardized job object
     *
     * @param array $payload
     *
     * @return \JobBrander\Jobs\Client\Job
     */
    public function createJobObject($payload)
    {
        echo "<pre>"; print_r($payload); exit;
        $defaults = [
            'title',
            'link',
            'description',
            'date',
            'enclosure',
        ];

        $payload = static::parseAttributeDefaults($payload, $defaults);

        $job = new Job([
            'description' => $payload['DescriptionTeaser'],
            'title' => $payload['JobTitle'],
            'url' => $payload['JobDetailsURL'],
            'company' => $payload['Company'],
            'location' => $payload['Location'],
        ]);

        return $job;
    }

    /**
     * Offset used for queries
     *
     * @return string
     */
    public function getOffset()
    {
        return ($this->page * $this->count);
    }

    /**
     * Get data format
     *
     * @return string
     */
    public function getRssFormat()
    {
        return 'rss';
    }

    /**
     * CL only returns 100 records at a time
     *
     * @return string
     */
    public function getCount()
    {
        return 100;
    }

    /**
     * Get data format
     *
     * @return string
     */
    public function getFormat()
    {
        return 'xml';
    }

    /**
     * Get listings path
     *
     * @return  string
     */
    public function getListingsPath()
    {
        return 'channel.item';
    }

    /**
     * Get parameters
     *
     * @return  array
     */
    public function getParameters()
    {
        return [];
    }

    /**
     * Get query string for client based on properties
     *
     * @return string
     */
    public function getQueryString()
    {
        $query_params = [
            'format' => 'getRssFormat',
            'query' => 'getKeyword',
            's' => 'getOffset',
        ];

        $query_string = [];

        array_walk($query_params, function ($value, $key) use (&$query_string) {
            $computed_value = $this->$value();
            if (!is_null($computed_value)) {
                $query_string[$key] = $computed_value;
            }
        });

        return http_build_query($query_string);
    }

    /**
     * Get url
     *
     * @return  string
     */
    public function getUrl()
    {
        $query_string = $this->getQueryString();

        return 'http://chicago.craigslist.org/search/jjj?'.$query_string;
    }

    /**
     * Get http verb
     *
     * @return  string
     */
    public function getVerb()
    {
        return 'GET';
    }
}
