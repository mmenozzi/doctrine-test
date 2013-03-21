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
        $this->recreateSchema();
    }

    private function initEntityManager()
    {
        $this->entityManager = EntityManager::create($this->conn, $this->config);
    }

    private function recreateSchema()
    {
        $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metadatas);
    }

    public function testSingleFlush()
    {

        // We create an Order with related PurchasedServicesPackage that is related with 2 PurchasedService.
        $order = new \DoctrineTest\Entity\Order();
        $order->setName('My ORDER');

        $purchasedServicesPackage = $this->createNewPurchasedServicesPackage($this->entityManager);
        $order->setPurchasedServicesPackage($purchasedServicesPackage);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // So, we assert that there is only one PurchasedServicesPackage and 2 PurchasedService.
        $this->assertCount(
            1,
            $this->entityManager->getRepository('DoctrineTest\Entity\PurchasedServicesPackage')->findAll()
        );
        $this->assertCount(
            2,
            $this->entityManager->getRepository('DoctrineTest\Entity\PurchasedService')->findAll()
        );

        // Re-init of EntityManager is needed to simulate, for example, another HTTP request.
        $this->entityManager->close();
        $this->initEntityManager();

        // Now we load previously persisted Order and associate it with a new PurchasedServicesPackage with 2 new
        // PurchasedService.
        $order = $this->entityManager->getRepository('DoctrineTest\Entity\Order')->find(1);

        $purchasedServicesPackage = $this->createNewPurchasedServicesPackage($this->entityManager);
        $order->setPurchasedServicesPackage($purchasedServicesPackage);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // We assert again that there is only one PurchasedServicesPackage and 2 PurchasedService. This is because
        // we have orphanRemoval on Order::purchased_services_package and cascade="remove" on
        // PurchasedServicesPackage::services.
        $this->assertCount(
            1,
            $this->entityManager->getRepository('DoctrineTest\Entity\PurchasedServicesPackage')->findAll()
        );
        $this->assertCount(
            2,
            $this->entityManager->getRepository('DoctrineTest\Entity\PurchasedService')->findAll()
        );
    }

    public function testDoubleFlush()
    {
        // In this test we do exactly the same thing of testSingleFlush. The only difference is that we call two times
        // $this->entityManager->flush();
        $order = new \DoctrineTest\Entity\Order();
        $order->setName('My ORDER');

        $purchasedServicesPackage = $this->createNewPurchasedServicesPackage($this->entityManager);
        $order->setPurchasedServicesPackage($purchasedServicesPackage);

        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $this->entityManager->flush();

        // The first time there is no problem. These assetions are green.
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
        $this->entityManager->flush(); // <--- This is the problem!

        // With a second flush here something in orphanRemoval doesn't work and we have 2 PurchasedServicesPackage
        // and 4 PurchasedService!
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
