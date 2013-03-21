<?php
/**
 * @author Manuele Menozzi <mmenozzi@webgriffe.com> 
 */

namespace DoctrineTest\Entity;


/**
 * @Entity @Table(name="order_table")
 **/
class Order
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $name;

    /**
     * @OneToOne(
     *   targetEntity="DoctrineTest\Entity\PurchasedServicesPackage",
     *   inversedBy="order",
     *   cascade={"persist", "remove"},
     *   orphanRemoval=true
     * )
     */
    protected $purchased_services_package;

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

    public function setPurchasedServicesPackage(PurchasedServicesPackage $purchased_services_package)
    {
        $this->purchased_services_package = $purchased_services_package;
    }

    /**
     * @return PurchasedServicesPackage
     */
    public function getPurchasedServicesPackage()
    {
        return $this->purchased_services_package;
    }

}