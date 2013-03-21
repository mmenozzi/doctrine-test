<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com> 
 */

namespace DoctrineTest\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="purchased_services_package")
 **/
class PurchasedServicesPackage
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $name;

    /**
     * @OneToOne(
     *   targetEntity="DoctrineTest\Entity\Order",
     *   mappedBy="purchased_services_package"
     * )
     */
    protected $order;

    /**
     * @OneToMany(
     *   targetEntity="DoctrineTest\Entity\PurchasedService",
     *   mappedBy="package",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $services;

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setOrder(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addService(PurchasedService $service)
    {
        $service->setPackage($this);
        $this->services->add($service);
    }

    public function getServices()
    {
        return $this->services;
    }

}