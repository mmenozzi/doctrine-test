<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com> 
 */

namespace DoctrineTest\Tests;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use DoctrineTest\Entity\Order;

class DoubleFlushTest extends \PHPUnit_Framework_TestCase
{
    protected $conn;
    protected $config;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    protected function setUp()
    {
        global $conn;
        global $config;
        $this->conn = $conn;
        $this->config = $config;
        $this->initEntityManager();
    }

    private function initEntityManager()
    {
        $this->entityManager = EntityManager::create($this->conn, $this->config);
    }

    public function testDoubleFlush()
    {
        $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metadatas);

        $purchasedServicesPackage = $this->createNewPurchasedServicesPackage($this->entityManager);

        $order = new \DoctrineTest\Entity\Order();
        $order->setName('My ORDER');
        $order->setPurchasedServicesPackage($purchasedServicesPackage);

        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $this->entityManager->flush();

        $this->assertCount(
            1,
            $this->entityManager->getRepository('DoctrineTest\Entity\PurchasedServicesPackage')->findAll()
        );
        $this->assertCount(
            2,
            $this->entityManager->getRepository('DoctrineTest\Entity\PurchasedService')->findAll()
        );

        // Reinitializing EntityManager to simulate, for example, another HTTP request.
        $this->entityManager->close();
        $this->initEntityManager();

        $order = $this->entityManager->getRepository('DoctrineTest\Entity\Order')->find(1);

        $purchasedServicesPackage = $this->createNewPurchasedServicesPackage($this->entityManager);
        $order->setPurchasedServicesPackage($purchasedServicesPackage);

        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $this->entityManager->flush();

        $this->assertCount(
            1,
            $this->entityManager->getRepository('DoctrineTest\Entity\PurchasedServicesPackage')->findAll()
        );
        $this->assertCount(
            2,
            $this->entityManager->getRepository('DoctrineTest\Entity\PurchasedService')->findAll()
        );
    }

    private function createNewPurchasedServicesPackage()
    {
        $purchasedServiceA = new \DoctrineTest\Entity\PurchasedService();
        $purchasedServiceA->setName('BASE SERVICE A');

        $purchasedServiceB = new \DoctrineTest\Entity\PurchasedService();
        $purchasedServiceB->setName('BASE SERVICE B');

        $purchasedServicesPackage = new \DoctrineTest\Entity\PurchasedServicesPackage();
        $purchasedServicesPackage->setName('SERVICES PACKAGE');
        $purchasedServicesPackage->addService($purchasedServiceA);
        $purchasedServicesPackage->addService($purchasedServiceB);

        return $purchasedServicesPackage;
    }
}
