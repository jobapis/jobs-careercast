<?php namespace JobBrander\Jobs\Client\Providers;

use JobBrander\Jobs\Client\Job;

class Careercast extends AbstractProvider
{
    /**
     * Map of setter methods to query parameters
     *
     * @var array
     */
    protected $queryMap = [
        'setRows' => 'rows',
        'setPage' => 'page',
        'setRadius' => 'radius',
        'setNormalizedJobTitle' => 'normalizedJobTitle',
        'setCategory' => 'category',
        'setCompany' => 'company',
        'setJobSource' => 'jobSource',
        'setPostDate' => 'postDate',
        'setFormat' => 'format',
        'setWorkStatus' => 'workStatus',
        'setLocation' => 'location',
        'setKwsJobTitleOnly' => 'kwsJobTitleOnly',
        'setCount' => 'rows',
    ];

    /**
     * Current url query parameters
     *
     * @var array
     */
    protected $queryParams = [
        'rows' => null,
        'page' => null,
        'radius' => null,
        'normalizedJobTitle' => null,
        'category' => null,
        'company' => null,
        'jobSource' => null,
        'postDate' => null,
        'format' => 'rss',
        'workStatus' => null,
        'location' => null,
        'kwsJobTitleOnly' => null,
    ];

    /**
     * Create new Careercast jobs client.
     *
     * @param array $parameters
     */
    public function __construct($parameters = [])
    {
        parent::__construct($parameters);
        array_walk($parameters, [$this, 'updateQuery']);
    }

    /**
     * Magic method to handle get and set methods for properties
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (isset($this->queryMap[$method], $parameters[0])) {
            $this->updateQuery($parameters[0], $this->queryMap[$method]);
        }
        return parent::__call($method, $parameters);
    }

    /**
     * Returns the standardized job object
     *
     * @param array $payload
     *
     * @return \JobBrander\Jobs\Client\Job
     */
    public function createJobObject($payload)
    {
        $defaults = [
            'title',
            'link',
            'description',
            'pubDate',
        ];

        $payload = static::parseAttributeDefaults($payload, $defaults);

        $job = new Job([
            'description' => $payload['description'],
            'title' => $payload['title'],
            'name' => $payload['title'],
            'url' => $payload['link'],
            'query' => $this->keyword,
            'source' => $this->getSource(),
            'location' => $this->getLocation(),
        ]);

        $job->setDatePostedAsString($payload['pubDate']);

        if (isset($this->city)) {
            $job->setCity($this->city);
        }
        if (isset($this->state)) {
            $job->setState($this->state);
        }
        $job->setCompany($this->parseCompanyFromDescription($job->getDescription()));

        return $job;
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
     * Get query string for client based on properties
     *
     * @return string
     */
    public function getQueryString()
    {
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
        if ($this->getKeyword()) {
            $keyword = urlencode($this->getKeyword());
        } else {
            $keyword = urlencode(' ');
        }

        return 'http://www.careercast.com/jobs/results/keyword/'.$keyword.'?'.$query_string;
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

    /**
     * Attempt to get the company name from the description
     *
     * @return  string
     */
    public function parseCompanyFromDescription($description)
    {
        $array = explode(' - ', $description);
        if (isset($array[0]) && isset($array[1])) {
            return $array[0];
        }
        return null;
    }

    /**
     * Attempts to update current query parameters.
     *
     * @param  string  $value
     * @param  string  $key
     *
     * @return Careercast
     */
    protected function updateQuery($value, $key)
    {
        if (array_key_exists($key, $this->queryParams)) {
            $this->queryParams[$key] = $value;
        }
        if (array_key_exists($key, $this->urlParams)) {
            $this->urlParams[$key] = $value;
        }
        return $this;
    }
}
