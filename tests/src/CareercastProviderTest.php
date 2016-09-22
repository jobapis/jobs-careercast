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
            'Description',
            'JobTitle',
            'Url',
            'Id',
            'PostDate',
            'ExpireDate',
            'Requirements',
            'SalaryMax',
            'SalaryMin',
            'SalaryMin',
            'CategoryDisplay',
            'WorkStatusDisplay',
        ];
        $this->assertEquals($fields, $this->client->getDefaultResponseFields());
    }

    public function testItCanGetListingsPath()
    {
        $this->assertEquals('Jobs', $this->client->getListingsPath());
    }

    /*
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
    */

    /**
     * Integration test for the client's getJobs() method.
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
     */

    /**
     * Integration test with actual API call to the provider.
     */
    public function testItCanGetJobsFromApi()
    {
        if (!getenv('REAL_CALL')) {
            $this->markTestSkipped('REAL_CALL not set. Real API call will not be made.');
        }

        $keyword = 'sales';

        $query = new CareercastQuery([
            'keyword' => $keyword,
        ]);

        $client = new CareercastProvider($query);

        $results = $client->getJobs();
        var_dump($results); exit;

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
