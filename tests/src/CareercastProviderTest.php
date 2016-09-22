<?php namespace JobApis\Jobs\Client\Test;

use JobApis\Jobs\Client\Collection;
use JobApis\Jobs\Client\Job;
use JobApis\Jobs\Client\Providers\CareercastProvider;
use JobApis\Jobs\Client\Queries\CareercastQuery;
use Mockery as m;

class CareercastProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->query = m::mock('JobApis\Jobs\Client\Queries\CareercastQuery');

        $this->client = new CareercastProvider($this->query);
    }

    public function testItCanGetDefaultResponseFields()
    {
        $fields = [
            'title',
            'link',
            'description',
            'pubDate',
        ];
        $this->assertEquals($fields, $this->client->getDefaultResponseFields());
    }

    public function testItCanGetListingsPath()
    {
        $this->assertEquals('Results.JobSearchResult', $this->client->getListingsPath());
    }

    public function testItCanGetResponseFormatXml()
    {
        $this->assertEquals('xml', $this->client->getFormat());
    }

    public function testItReturnsSalaryWhenInputIsYearlyRange()
    {
        $min = rand(10, 1000);
        $max = $min * rand(1, 10);
        $string = "$".$min."k - $".$max."k/year";
        $result = $this->client->parseSalariesFromString($string);
        $this->assertEquals('$'.$min * 1000, $result['min']);
        $this->assertEquals('$'.$max * 1000, $result['max']);
    }

    public function testItReturnsSalaryWhenInputIsYearly()
    {
        $min = rand(10, 1000);
        $string = "$".$min."k/year";
        $result = $this->client->parseSalariesFromString($string);
        $this->assertEquals('$'.$min * 1000, $result['min']);
        $this->assertNull($result['max']);
    }

    public function testItReturnsSalaryWhenInputIsHourlyRange()
    {
        $min = rand(7, 100);
        $max = $min * rand(2, 5);
        $string = "$".$min.".00 - $".$max.".00/hour";
        $result = $this->client->parseSalariesFromString($string);
        $this->assertEquals('$'.$min.'.00', $result['min']);
        $this->assertEquals('$'.$max.'.00', $result['max']);
    }

    public function testItReturnsSalaryWhenInputIsHourly()
    {
        $min = rand(10, 1000);
        $string = "$".$min.".00/hour";
        $result = $this->client->parseSalariesFromString($string);
        $this->assertEquals('$'.$min.'.00', $result['min']);
        $this->assertNull($result['max']);
    }

    public function testItReturnsNullSalaryWhenInputNA()
    {
        $string = "N/A";
        $result = $this->client->parseSalariesFromString($string);
        $this->assertNull($result['min']);
        $this->assertNull($result['max']);
    }

    public function testItReturnsNullSalaryWhenInputIsOther()
    {
        $string = uniqid();
        $result = $this->client->parseSalariesFromString($string);
        $this->assertNull($result['min']);
        $this->assertNull($result['max']);
    }

    public function testItCanCreateJobObjectFromPayload()
    {
        $payload = $this->createJobArray();

        $results = $this->client->createJobObject($payload);

        $this->assertInstanceOf(Job::class, $results);
        $this->assertEquals($payload['JobTitle'], $results->title);
        $this->assertEquals($payload['DescriptionTeaser'], $results->description);
        $this->assertEquals($payload['JobDetailsURL'], $results->url);
    }

    public function testItCanCreateJobFromPayloadWhenSingleSkillProvided()
    {
        $payload = $this->createJobArrayWithSingleSkill();
        $results = $this->client->createJobObject($payload);
        $this->assertEquals($payload['JobTitle'], $results->title);
        $this->assertEquals($payload['DescriptionTeaser'], $results->description);
        $this->assertEquals($payload['JobDetailsURL'], $results->url);
    }

    public function testItCanCreateJobFromPayloadWhenInvalidSkillProvided()
    {
        $payload = $this->createJobArrayWithInvalidSkill();
        $results = $this->client->createJobObject($payload);
        $this->assertEquals($payload['JobTitle'], $results->title);
        $this->assertEquals($payload['DescriptionTeaser'], $results->description);
        $this->assertEquals($payload['JobDetailsURL'], $results->url);
    }

    /**
     * Integration test for the client's getJobs() method.
     */
    public function testItCanGetJobs()
    {
        $options = [
            'Keywords' => uniqid(),
            'FacetCity' => uniqid(),
            'DeveloperKey' => uniqid(),
        ];

        $guzzle = m::mock('GuzzleHttp\Client');

        $query = new CareerbuilderQuery($options);

        $client = new CareerbuilderProvider($query);

        $client->setClient($guzzle);

        $response = m::mock('GuzzleHttp\Message\Response');

        $jobs = $this->getXmlJobs();

        $guzzle->shouldReceive('get')
            ->with($query->getUrl(), [])
            ->once()
            ->andReturn($response);
        $response->shouldReceive('getBody')
            ->once()
            ->andReturn($jobs);

        $results = $client->getJobs();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertCount(3, $results);
    }

    /**
     * Integration test with actual API call to the provider.
     */
    public function testItCanGetJobsFromApi()
    {
        if (!getenv('DEVELOPER_KEY')) {
            $this->markTestSkipped('DEVELOPER_KEY not set. Real API call will not be made.');
        }

        $keyword = 'engineering';

        $query = new CareerbuilderQuery([
            'Keywords' => $keyword,
            'DeveloperKey' => getenv('DEVELOPER_KEY'),
        ]);

        $client = new CareerbuilderProvider($query);

        $results = $client->getJobs();

        $this->assertInstanceOf('JobApis\Jobs\Client\Collection', $results);

        foreach($results as $job) {
            $this->assertEquals($keyword, $job->query);
        }
    }

    private function createJobArray() {
        return [
            'Company' => uniqid(),
            'CompanyDetailsURL' => uniqid(),
            'DescriptionTeaser' => uniqid(),
            'DID' => uniqid(),
            'OnetCode' => uniqid(),
            'ONetFriendlyTitle' => uniqid(),
            'EmploymentType' => uniqid(),
            'EducationRequired' => uniqid(),
            'ExperienceRequired' => uniqid(),
            'JobDetailsURL' => uniqid(),
            'Location' => uniqid(),
            'City' => uniqid(),
            'State' => uniqid(),
            'PostedDate' => date('m/d/Y'),
            'Pay' => uniqid(),
            'JobTitle' => uniqid(),
            'CompanyImageURL' => uniqid(),
            'Skills' => [
                'Skill' => [
                    0 => uniqid(),
                    1 => uniqid(),
                    2 => uniqid(),
                    3 => uniqid(),
                ]
            ]
        ];
    }

    private function createJobArrayWithSingleSkill() {
        return [
            'Company' => uniqid(),
            'CompanyDetailsURL' => uniqid(),
            'DescriptionTeaser' => uniqid(),
            'DID' => uniqid(),
            'OnetCode' => uniqid(),
            'ONetFriendlyTitle' => uniqid(),
            'EmploymentType' => uniqid(),
            'EducationRequired' => uniqid(),
            'ExperienceRequired' => uniqid(),
            'JobDetailsURL' => uniqid(),
            'Location' => uniqid(),
            'City' => uniqid(),
            'State' => uniqid(),
            'PostedDate' => date('m/d/Y'),
            'Pay' => uniqid(),
            'JobTitle' => uniqid(),
            'CompanyImageURL' => uniqid(),
            'Skills' => [
                'Skill' =>  uniqid()
            ]
        ];
    }

    private function createJobArrayWithInvalidSkill() {
        return [
            'Company' => uniqid(),
            'CompanyDetailsURL' => uniqid(),
            'DescriptionTeaser' => uniqid(),
            'DID' => uniqid(),
            'OnetCode' => uniqid(),
            'ONetFriendlyTitle' => uniqid(),
            'EmploymentType' => uniqid(),
            'EducationRequired' => uniqid(),
            'ExperienceRequired' => uniqid(),
            'JobDetailsURL' => uniqid(),
            'Location' => uniqid(),
            'City' => uniqid(),
            'State' => uniqid(),
            'PostedDate' => date('m/d/Y'),
            'Pay' => uniqid(),
            'JobTitle' => uniqid(),
            'CompanyImageURL' => uniqid(),
            'Skills' => [
                'Skill' => new \stdClass()
            ]
        ];
    }

    private function getJobs()
    {
        return null;
    }
}
