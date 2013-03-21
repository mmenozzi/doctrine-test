<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com> 
 */

namespace DoctrineTest\Entity;

/**
 * @Entity @Table(name="purchased_service")
 **/
class PurchasedService
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $name;

    /**
     * @ManyToOne(targetEntity="DoctrineTest\Entity\PurchasedServicesPackage", inversedBy="services")
     */
    protected $package;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPackage(PurchasedServicesPackage $package)
    {
        $this->package = $package;
    }

    /**
     * @return PurchasedServicesPackage
     */
    public function getPackage()
    {
        return $this->package;
    }
}