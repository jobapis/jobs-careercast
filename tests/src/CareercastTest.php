<?php namespace JobBrander\Jobs\Client\Providers\Test;

use JobBrander\Jobs\Client\Providers\Careercast;
use Mockery as m;

class CareercastTest extends \PHPUnit_Framework_TestCase
{
    private $clientClass = 'JobBrander\Jobs\Client\Providers\AbstractProvider';
    private $collectionClass = 'JobBrander\Jobs\Client\Collection';
    private $jobClass = 'JobBrander\Jobs\Client\Job';

    public function setUp()
    {
        $this->client = new Careercast();
    }

    public function testItWillUseXmlFormat()
    {
        $format = $this->client->getFormat();

        $this->assertEquals('xml', $format);
    }

    public function testItWillGetRssFormat()
    {
        $format = $this->client->getRssFormat();

        $this->assertEquals('rss', $format);
    }

    public function testItWillUseGetHttpVerb()
    {
        $verb = $this->client->getVerb();

        $this->assertEquals('GET', $verb);
    }

    public function testListingPath()
    {
        $path = $this->client->getListingsPath();

        $this->assertEquals('channel.item', $path);
    }

    public function testItWillProvideEmptyParameters()
    {
        $parameters = $this->client->getParameters();

        $this->assertEmpty($parameters);
        $this->assertTrue(is_array($parameters));
    }

    public function testItWillGetLocationWhenCityAndStateProvided()
    {
        $city = uniqid();
        $state = uniqid();

        $this->client->setCity($city);
        $this->client->setState($state);

        $location = $this->client->getLocation();

        $this->assertEquals($city.', '.$state, $location);
    }

    public function testItWillGetLocationWhenCityProvided()
    {
        $city = uniqid();
        $this->client->setCity($city);

        $location = $this->client->getLocation();

        $this->assertEquals($city, $location);
    }

    public function testItWillGetLocationWhenStateProvided()
    {
        $state = uniqid();
        $this->client->setState($state);

        $location = $this->client->getLocation();

        $this->assertEquals($state, $location);
    }

    public function testItWillNotGetLocationWhenNoneProvided()
    {
        $location = $this->client->getLocation();

        $this->assertNull($location);
    }

    public function testItWillGetCountWhenUnderFifty()
    {
        $count = rand(1, 50);
        $this->client->setCount($count);

        $results = $this->client->getCount();

        $this->assertEquals($count, $results);
    }

    public function testItWillGetCountWhenOverFifty()
    {
        $count = rand(51, 200);
        $this->client->setCount($count);

        $results = $this->client->getCount();

        $this->assertEquals(50, $results);
    }

    public function testUrlIncludesKeywordWhenProvided()
    {
        $keyword = uniqid().' '.uniqid();
        $param = 'keyword/'.urlencode($keyword);

        $url = $this->client->setKeyword($keyword)->getUrl();

        $this->assertContains($param, $url);
    }

    public function testUrlNotIncludesKeywordWhenNotProvided()
    {
        $defaultKeyword = ' ';
        $param = 'keyword/'.urlencode($defaultKeyword);

        $url = $this->client->getUrl();

        $this->assertContains($param, $url);
    }

    public function testUrlIncludesLocationWhenCityAndStateProvided()
    {
        $city = uniqid();
        $state = uniqid();
        $param = 'location='.urlencode($city.', '.$state);

        $url = $this->client->setCity($city)->setState($state)->getUrl();

        $this->assertContains($param, $url);
    }

    public function testUrlNotIncludesLocationWhenNotProvided()
    {
        $param = 'location=';

        $url = $this->client->getUrl();

        $this->assertNotContains($param, $url);
    }

    public function testUrlIncludesPageWhenProvided()
    {
        $page = uniqid();
        $param = 'page='.$page;

        $url = $this->client->setPage($page)->getUrl();

        $this->assertContains($param, $url);
    }

    public function testUrlNotIncludesPageWhenNotProvided()
    {
        $param = 'page=';

        $url = $this->client->setPage(null)->getUrl();

        $this->assertNotContains($param, $url);
    }

    public function testUrlIncludesCountWhenProvided()
    {
        $count = rand(0, 50);
        $param = 'rows='.$count;

        $url = $this->client->setCount($count)->getUrl();

        $this->assertContains($param, $url);
    }

    public function testUrlNotIncludesStartWhenNotProvided()
    {
        $param = 'rows=';

        $url = $this->client->setCount(null)->getUrl();

        $this->assertNotContains($param, $url);
    }

    public function testItCanCreateJobFromPayload()
    {
        $city = uniqid();
        $state = uniqid();
        $this->client->setCity($city);
        $this->client->setState($state);
        $payload = $this->createJobArray();

        $results = $this->client->createJobObject($payload);

        $this->assertEquals($payload['title'], $results->title);
        $this->assertEquals($payload['description'], $results->description);
        $this->assertEquals($payload['link'], $results->url);
        $this->assertNotNull($results->company);
        $this->assertInstanceOf('DateTime', $results->datePosted);
        $this->assertEquals($city.', '.$state, $results->location);
    }

    public function testItCanCreateJobFromPayloadWithoutCompanyInDescription()
    {
        $city = uniqid();
        $state = uniqid();
        $this->client->setCity($city);
        $this->client->setState($state);
        $payload = $this->createJobArrayWithoutCompany();

        $results = $this->client->createJobObject($payload);

        $this->assertEquals($payload['title'], $results->title);
        $this->assertEquals($payload['description'], $results->description);
        $this->assertEquals($payload['link'], $results->url);
        $this->assertInstanceOf('DateTime', $results->datePosted);
        $this->assertEquals($city.', '.$state, $results->location);
    }

    public function testItCanConnect()
    {
        $provider = $this->getProviderAttributes(['format' => 'xml']);
        $payload = [];

        $responseBody = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><rss version=\"2.0\"><channel>";

        for ($i = 0; $i < $provider['jobs_count']; $i++) {
            $jobArray = $this->createJobArray();
            $path = $provider['path'];
            array_push($payload, $jobArray);
            $responseBody .= "<item><title>".$jobArray['title']."</title>";
            $responseBody .= "<pubDate>".$jobArray['pubDate']."</pubDate>";
            $responseBody .= "<description><![CDATA[ ".$jobArray['description']." ]]></description>";
            $responseBody .= "</item>";
        }

        $responseBody .= "</channel></rss>";

        $job = m::mock($this->jobClass);
        $job->shouldReceive('setQuery')->with($provider['keyword'])
            ->times($provider['jobs_count'])->andReturnSelf();
        $job->shouldReceive('setSource')->with($provider['source'])
            ->times($provider['jobs_count'])->andReturnSelf();

        $response = m::mock('GuzzleHttp\Message\Response');
        $response->shouldReceive('getBody')->once()->andReturn($responseBody);

        $http = m::mock('GuzzleHttp\Client');
        $http->shouldReceive(strtolower($this->client->getVerb()))
            ->with($this->client->getUrl(), $this->client->getHttpClientOptions())
            ->once()
            ->andReturn($response);
        $this->client->setClient($http);

        $results = $this->client->getJobs();

        $this->assertInstanceOf($this->collectionClass, $results);
        $this->assertCount($provider['jobs_count'], $results);
    }

    private function createJobArray() {
        return [
            'title' => uniqid(),
            'description' => uniqid().' - '.uniqid().' '.uniqid(),
            'link' => uniqid(),
            'pubDate' => date('F j, Y, g:i a'),
        ];
    }

    private function createJobArrayWithoutCompany() {
        return [
            'title' => uniqid(),
            'description' => uniqid(),
            'link' => uniqid(),
            'pubDate' => date('F j, Y, g:i a'),
        ];
    }

    private function getProviderAttributes($attributes = [])
    {
        $defaults = [
            'path' => uniqid(),
            'format' => 'json',
            'keyword' => uniqid(),
            'source' => uniqid(),
            'params' => [uniqid()],
            'jobs_count' => rand(2,10),

        ];
        return array_replace($defaults, $attributes);
    }
}
