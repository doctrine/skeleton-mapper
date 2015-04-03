<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper\Tests\HttpImplementation\ObjectDataRepository;
use Doctrine\SkeletonMapper\Tests\HttpImplementation\ObjectPersister;
use Doctrine\SkeletonMapper\Tests\DataTesterInterface;
use GuzzleHttp\Client;

class HttpImplementationTest extends BaseImplementationTest
{
    private $client;

    protected function setUpImplementation()
    {
        if ('Success' !== @file_get_contents('http://localhost/index.php')) {
            $this->markTestSkipped();
        }

        $this->client = new Client();

        $mongo = new \MongoClient();
        $database = $mongo->selectDB('httpimplementation');
        $database->drop();

        $users = $database->selectCollection('users');

        $users->batchInsert(array(
            array(
                '_id' => 1,
                'username' => 'jwage',
                'password' => 'password',
            ),
            array(
                '_id' => 2,
                'username' => 'romanb',
                'password' => 'password',
            ),
        ));

        $this->usersTester = new HttpTester($this->client, 'http://localhost/index.php/users');
        $this->profilesTester = new HttpTester($this->client, 'http://localhost/index.php/profiles');
        $this->groupsTester = new HttpTester($this->client, 'http://localhost/index.php/groups');

        $this->setUpCommon();
    }

    protected function createUserDataRepository()
    {
        return new ObjectDataRepository(
            $this->objectManager, $this->client, $this->userClassName, 'http://localhost/index.php/users'
        );
    }

    protected function createUserPersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->client, $this->userClassName, 'http://localhost/index.php/users'
        );
    }

    protected function createProfileDataRepository()
    {
        return new ObjectDataRepository(
            $this->objectManager, $this->client, $this->profileClassName, 'http://localhost/index.php/profiles'
        );
    }

    protected function createProfilePersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->client, $this->profileClassName, 'http://localhost/index.php/profiles'
        );
    }

    protected function createGroupDataRepository()
    {
        return new ObjectDataRepository(
            $this->objectManager, $this->client, $this->groupClassName, 'http://localhost/index.php/groups'
        );
    }

    protected function createGroupPersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->client, $this->groupClassName, 'http://localhost/index.php/groups'
        );
    }
}

class HttpTester implements DataTesterInterface
{
    private $client;
    private $url;

    public function __construct(Client $client, $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    public function find($id)
    {
        return $this->client->get(sprintf('%s/%s', $this->url, $id))->json();
    }

    public function set($id, $key, $value)
    {
        $data = array($key => $value);

        return $this->client->put(sprintf('%s/%s', $this->url, $id), array('body' => $data))->json();
    }

    public function count()
    {
        return count($this->client->get($this->url)->json());
    }
}
