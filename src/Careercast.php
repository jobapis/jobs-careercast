<?php namespace JobBrander\Jobs\Client\Providers;

use JobBrander\Jobs\Client\Job;

class Careercast extends AbstractProvider
{
    /**
     * Rss format string for query to Craigslist
     *
     * @var string
     */
    protected $rssFormat;

    /**
     * Combined city and state location
     *
     * @var string
     */
    protected $location;

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

        return $job;
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
     * Get data format
     *
     * @return string
     */
    public function getLocation()
    {
        if (isset($this->city) && isset($this->state)) {
            return $this->city. ', '. $this->state;
        }
        if (isset($this->city)) {
            return $this->city;
        }
        if (isset($this->state)) {
            return $this->state;
        }
        return null;
    }

    /**
     * Get number of results to return
     *
     * @return string
     */
    public function getCount()
    {
        if ($this->count > 50) {
            return 50;
        }
        return $this->count;
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
            'rows' => 'getCount',
            'page' => 'getPage',
            'location' => 'getLocation',
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
     * Attempt to parse as XML
     *
     * @param  string $string
     *
     * @return array
     */
    private function parseAsXml($string)
    {
        try {
            return json_decode(
                json_encode(
                    simplexml_load_string(
                        $string, null, LIBXML_NOCDATA
                    )
                ),
                true
            );
        } catch (\Exception $e) {
        }

        return [];
    }
}
