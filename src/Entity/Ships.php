<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="ships")
 * @ORM\Entity
 */
class Ships
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="User", mappedBy="ship", fetch="EXTRA_LAZY")
     */
    protected $user;

    /**
     * @ORM\Column(name="pointHunter",type="smallint", options={"unsigned":true})
     */
    protected $pointHunter;

    /**
     * @ORM\Column(name="armorHunter",type="smallint", options={"unsigned":true})
     */
    protected $armorHunter;

    /**
     * @ORM\Column(name="accurateHunter",type="smallint", options={"unsigned":true})
     */
    protected $accurateHunter;

    /**
     * @ORM\Column(name="missileHunter",type="smallint", options={"unsigned":true})
     */
    protected $missileHunter;

    /**
     * @ORM\Column(name="pointHunterHeavy",type="smallint", options={"unsigned":true})
     */
    protected $pointHunterHeavy;

    /**
     * @ORM\Column(name="armorHunterHeavy",type="smallint", options={"unsigned":true})
     */
    protected $armorHunterHeavy;

    /**
     * @ORM\Column(name="accurateHunterHeavy",type="smallint", options={"unsigned":true})
     */
    protected $accurateHunterHeavy;

    /**
     * @ORM\Column(name="missileHunterHeavy",type="smallint", options={"unsigned":true})
     */
    protected $missileHunterHeavy;

    /**
     * @ORM\Column(name="pointHunterWar",type="smallint", options={"unsigned":true})
     */
    protected $pointHunterWar;

    /**
     * @ORM\Column(name="armorHunterWar",type="smallint", options={"unsigned":true})
     */
    protected $armorHunterWar;

    /**
     * @ORM\Column(name="accurateHunterWar",type="smallint", options={"unsigned":true})
     */
    protected $accurateHunterWar;

    /**
     * @ORM\Column(name="missileHunterWar",type="smallint", options={"unsigned":true})
     */
    protected $missileHunterWar;

    /**
     * @ORM\Column(name="laserHunterWar",type="smallint", options={"unsigned":true})
     */
    protected $laserHunterWar;

    /**
     * @ORM\Column(name="plasmaHunterWar",type="smallint", options={"unsigned":true})
     */
    protected $plasmaHunterWar;

    /**
     * @ORM\Column(name="pointCorvet",type="smallint", options={"unsigned":true})
     */
    protected $pointCorvet;

    /**
     * @ORM\Column(name="armorCorvet",type="smallint", options={"unsigned":true})
     */
    protected $armorCorvet;

    /**
     * @ORM\Column(name="accurateCorvet",type="smallint", options={"unsigned":true})
     */
    protected $accurateCorvet;

    /**
     * @ORM\Column(name="missileCorvet",type="smallint", options={"unsigned":true})
     */
    protected $missileCorvet;

    /**
     * @ORM\Column(name="shieldCorvet",type="smallint", options={"unsigned":true})
     */
    protected $shieldCorvet;

    /**
     * @ORM\Column(name="pointCorvetLaser",type="smallint", options={"unsigned":true})
     */
    protected $pointCorvetLaser;

    /**
     * @ORM\Column(name="armorCorvetLaser",type="smallint", options={"unsigned":true})
     */
    protected $armorCorvetLaser;

    /**
     * @ORM\Column(name="accurateCorveLasert",type="smallint", options={"unsigned":true})
     */
    protected $accurateCorvetLaser;

    /**
     * @ORM\Column(name="missileCorvetLaser",type="smallint", options={"unsigned":true})
     */
    protected $missileCorvetLaser;

    /**
     * @ORM\Column(name="laserCorvetLaser",type="smallint", options={"unsigned":true})
     */
    protected $laserCorvetLaser;

    /**
     * @ORM\Column(name="shieldCorvetLaser",type="smallint", options={"unsigned":true})
     */
    protected $shieldCorvetLaser;

    /**
     * @ORM\Column(name="pointCorvetWar",type="smallint", options={"unsigned":true})
     */
    protected $pointCorvetWar;

    /**
     * @ORM\Column(name="armorCorvetWar",type="smallint", options={"unsigned":true})
     */
    protected $armorCorvetWar;

    /**
     * @ORM\Column(name="accurateCorvetWar",type="smallint", options={"unsigned":true})
     */
    protected $accurateCorvetWar;

    /**
     * @ORM\Column(name="missileCorvetWar",type="smallint", options={"unsigned":true})
     */
    protected $missileCorvetWar;

    /**
     * @ORM\Column(name="laserCorvetWar",type="smallint", options={"unsigned":true})
     */
    protected $laserCorvetWar;

    /**
     * @ORM\Column(name="shieldCorvetWar",type="smallint", options={"unsigned":true})
     */
    protected $shieldCorvetWar;

    /**
     * @ORM\Column(name="pointFregate",type="smallint", options={"unsigned":true})
     */
    protected $pointFregate;

    /**
     * @ORM\Column(name="armorFregate",type="smallint", options={"unsigned":true})
     */
    protected $armorFregate;

    /**
     * @ORM\Column(name="accurateFregate",type="smallint", options={"unsigned":true})
     */
    protected $accurateFregate;

    /**
     * @ORM\Column(name="missileFregate",type="smallint", options={"unsigned":true})
     */
    protected $missileFregate;

    /**
     * @ORM\Column(name="laserFregate",type="smallint", options={"unsigned":true})
     */
    protected $laserFregate;

    /**
     * @ORM\Column(name="shieldFregate",type="smallint", options={"unsigned":true})
     */
    protected $shieldFregate;

    /**
     * @ORM\Column(name="pointFregatePlasma",type="smallint", options={"unsigned":true})
     */
    protected $pointFregatePlasma;

    /**
     * @ORM\Column(name="armorFregatePlasma",type="smallint", options={"unsigned":true})
     */
    protected $armorFregatePlasma;

    /**
     * @ORM\Column(name="accurateFregatePlasma",type="smallint", options={"unsigned":true})
     */
    protected $accurateFregatePlasma;

    /**
     * @ORM\Column(name="missileFregatePlasma",type="smallint", options={"unsigned":true})
     */
    protected $missileFregatePlasma;

    /**
     * @ORM\Column(name="laserFregatePlasma",type="smallint", options={"unsigned":true})
     */
    protected $laserFregatePlasma;

    /**
     * @ORM\Column(name="shieldFregatePlasma",type="smallint", options={"unsigned":true})
     */
    protected $shieldFregatePlasma;

    /**
     * @ORM\Column(name="plasmaFregatePlasma",type="smallint", options={"unsigned":true})
     */
    protected $plasmaFregatePlasma;

    /**
     * @ORM\Column(name="pointCroiser",type="smallint", options={"unsigned":true})
     */
    protected $pointCroiser;

    /**
     * @ORM\Column(name="armorCroiser",type="smallint", options={"unsigned":true})
     */
    protected $armorCroiser;

    /**
     * @ORM\Column(name="accurateCroiser",type="smallint", options={"unsigned":true})
     */
    protected $accurateCroiser;

    /**
     * @ORM\Column(name="missileCroiser",type="smallint", options={"unsigned":true})
     */
    protected $missileCroiser;

    /**
     * @ORM\Column(name="laserCroiser",type="smallint", options={"unsigned":true})
     */
    protected $laserCroiser;

    /**
     * @ORM\Column(name="shieldCroiser",type="smallint", options={"unsigned":true})
     */
    protected $shieldCroiser;

    /**
     * @ORM\Column(name="plasmaCroiser",type="smallint", options={"unsigned":true})
     */
    protected $plasmaCroiser;

    /**
     * @ORM\Column(name="pointIronClad",type="smallint", options={"unsigned":true})
     */
    protected $pointIronClad;

    /**
     * @ORM\Column(name="armorIronClad",type="smallint", options={"unsigned":true})
     */
    protected $armorIronClad;

    /**
     * @ORM\Column(name="accurateIronClad",type="smallint", options={"unsigned":true})
     */
    protected $accurateIronClad;

    /**
     * @ORM\Column(name="missileIronClad",type="smallint", options={"unsigned":true})
     */
    protected $missileIronClad;

    /**
     * @ORM\Column(name="laserIronClad",type="smallint", options={"unsigned":true})
     */
    protected $laserIronClad;

    /**
     * @ORM\Column(name="shieldIronClad",type="smallint", options={"unsigned":true})
     */
    protected $shieldIronClad;

    /**
     * @ORM\Column(name="plasmaIronClad",type="smallint", options={"unsigned":true})
     */
    protected $plasmaIronClad;

    /**
     * @ORM\Column(name="pointDestroyer",type="smallint", options={"unsigned":true})
     */
    protected $pointDestroyer;

    /**
     * @ORM\Column(name="armorDestroyer",type="smallint", options={"unsigned":true})
     */
    protected $armorDestroyer;

    /**
     * @ORM\Column(name="accurateDestroyer",type="smallint", options={"unsigned":true})
     */
    protected $accurateDestroyer;

    /**
     * @ORM\Column(name="missileDestroyer",type="smallint", options={"unsigned":true})
     */
    protected $missileDestroyer;

    /**
     * @ORM\Column(name="laserDestroyer",type="smallint", options={"unsigned":true})
     */
    protected $laserDestroyer;

    /**
     * @ORM\Column(name="shieldDestroyer",type="smallint", options={"unsigned":true})
     */
    protected $shieldDestroyer;

    /**
     * @ORM\Column(name="plasmaDestroyer",type="smallint", options={"unsigned":true})
     */
    protected $plasmaDestroyer;

    /**
     * @ORM\Column(name="lastUpdate",type="datetime", nullable=true)
     */
    protected $lastUpdate;

    /**
     * @ORM\Column(name="max",type="smallint", options={"unsigned":true})
     */
    protected $max;

    /**
     * @ORM\Column(name="retry",type="smallint", options={"unsigned":true})
     */
    protected $retry;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->pointHunter = 3;
        $this->armorHunter = 5;
        $this->accurateHunter = 0;
        $this->missileHunter = 1;
        $this->pointHunterHeavy = 5;
        $this->armorHunterHeavy = 10;
        $this->accurateHunterHeavy = 0;
        $this->missileHunterHeavy = 2;
        $this->pointHunterWar = 20;
        $this->armorHunterWar = 20;
        $this->accurateHunterWar = 0;
        $this->missileHunterWar = 0;
        $this->laserHunterWar = 0;
        $this->plasmaHunterWar = 0;
        $this->pointCorvet = 12;
        $this->armorCorvet = 40;
        $this->accurateCorvet = 0;
        $this->missileCorvet = 6;
        $this->shieldCorvet = 1;
        $this->pointCorvetLaser = 20;
        $this->armorCorvetLaser = 65;
        $this->accurateCorvetLaser = 0;
        $this->missileCorvetLaser = 9;
        $this->laserCorvetLaser = 2;
        $this->shieldCorvetLaser = 2;
        $this->pointCorvetWar = 30;
        $this->armorCorvetWar = 75;
        $this->accurateCorvetWar = 0;
        $this->missileCorvetWar = 15;
        $this->laserCorvetWar = 4;
        $this->shieldCorvetWar = 4;
        $this->pointFregate = 25;
        $this->armorFregate = 110;
        $this->accurateFregate = 0;
        $this->missileFregate = 22;
        $this->laserFregate = 3;
        $this->shieldFregate = 5;
        $this->pointFregatePlasma = 55;
        $this->armorFregatePlasma = 300;
        $this->accurateFregatePlasma = 0;
        $this->missileFregatePlasma = 27;
        $this->laserFregatePlasma = 3;
        $this->shieldFregatePlasma = 7;
        $this->plasmaFregatePlasma = 12;
        $this->pointCroiser = 100;
        $this->armorCroiser = 640;
        $this->accurateCroiser = 0;
        $this->missileCroiser = 40;
        $this->laserCroiser = 10;
        $this->shieldCroiser = 10;
        $this->plasmaCroiser = 5;
        $this->pointIronClad = 275;
        $this->armorIronClad = 1250;
        $this->accurateIronClad = 0;
        $this->missileIronClad = 50;
        $this->laserIronClad = 8;
        $this->shieldIronClad = 12;
        $this->plasmaIronClad = 8;
        $this->pointDestroyer = 630;
        $this->armorDestroyer = 2600;
        $this->accurateDestroyer = 0;
        $this->missileDestroyer = 100;
        $this->laserDestroyer = 0;
        $this->shieldDestroyer = 120;
        $this->plasmaDestroyer = 0;
        $this->lastUpdate = null;
        $this->max = 40;
        $this->retry = 3;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getArmorHunter()
    {
        return $this->armorHunter;
    }

    /**
     * @param mixed $armorHunter
     */
    public function setArmorHunter($armorHunter): void
    {
        $this->armorHunter = $armorHunter;
    }

    /**
     * @return mixed
     */
    public function getAccurateHunter()
    {
        return $this->accurateHunter;
    }

    /**
     * @param mixed $accurateHunter
     */
    public function setAccurateHunter($accurateHunter): void
    {
        $this->accurateHunter = $accurateHunter;
    }

    /**
     * @return mixed
     */
    public function getMissileHunter()
    {
        return $this->missileHunter;
    }

    /**
     * @param mixed $missileHunter
     */
    public function setMissileHunter($missileHunter): void
    {
        $this->missileHunter = $missileHunter;
    }

    /**
     * @return mixed
     */
    public function getPointHunter()
    {
        return $this->pointHunter;
    }

    /**
     * @param mixed $pointHunter
     */
    public function setPointHunter($pointHunter): void
    {
        $this->pointHunter = $pointHunter;
    }

    /**
     * @return mixed
     */
    public function getPointHunterHeavy()
    {
        return $this->pointHunterHeavy;
    }

    /**
     * @param mixed $pointHunterHeavy
     */
    public function setPointHunterHeavy($pointHunterHeavy): void
    {
        $this->pointHunterHeavy = $pointHunterHeavy;
    }

    /**
     * @return mixed
     */
    public function getArmorHunterHeavy()
    {
        return $this->armorHunterHeavy;
    }

    /**
     * @param mixed $armorHunterHeavy
     */
    public function setArmorHunterHeavy($armorHunterHeavy): void
    {
        $this->armorHunterHeavy = $armorHunterHeavy;
    }

    /**
     * @return mixed
     */
    public function getAccurateHunterHeavy()
    {
        return $this->accurateHunterHeavy;
    }

    /**
     * @param mixed $accurateHunterHeavy
     */
    public function setAccurateHunterHeavy($accurateHunterHeavy): void
    {
        $this->accurateHunterHeavy = $accurateHunterHeavy;
    }

    /**
     * @return mixed
     */
    public function getMissileHunterHeavy()
    {
        return $this->missileHunterHeavy;
    }

    /**
     * @param mixed $missileHunterHeavy
     */
    public function setMissileHunterHeavy($missileHunterHeavy): void
    {
        $this->missileHunterHeavy = $missileHunterHeavy;
    }

    /**
     * @return mixed
     */
    public function getPointHunterWar()
    {
        return $this->pointHunterWar;
    }

    /**
     * @param mixed $pointHunterWar
     */
    public function setPointHunterWar($pointHunterWar): void
    {
        $this->pointHunterWar = $pointHunterWar;
    }

    /**
     * @return mixed
     */
    public function getArmorHunterWar()
    {
        return $this->armorHunterWar;
    }

    /**
     * @param mixed $armorHunterWar
     */
    public function setArmorHunterWar($armorHunterWar): void
    {
        $this->armorHunterWar = $armorHunterWar;
    }

    /**
     * @return mixed
     */
    public function getAccurateHunterWar()
    {
        return $this->accurateHunterWar;
    }

    /**
     * @param mixed $accurateHunterWar
     */
    public function setAccurateHunterWar($accurateHunterWar): void
    {
        $this->accurateHunterWar = $accurateHunterWar;
    }

    /**
     * @return mixed
     */
    public function getMissileHunterWar()
    {
        return $this->missileHunterWar;
    }

    /**
     * @param mixed $missileHunterWar
     */
    public function setMissileHunterWar($missileHunterWar): void
    {
        $this->missileHunterWar = $missileHunterWar;
    }

    /**
     * @return mixed
     */
    public function getLaserHunterWar()
    {
        return $this->laserHunterWar;
    }

    /**
     * @param mixed $laserHunterWar
     */
    public function setLaserHunterWar($laserHunterWar): void
    {
        $this->laserHunterWar = $laserHunterWar;
    }

    /**
     * @return mixed
     */
    public function getPlasmaHunterWar()
    {
        return $this->plasmaHunterWar;
    }

    /**
     * @param mixed $plasmaHunterWar
     */
    public function setPlasmaHunterWar($plasmaHunterWar): void
    {
        $this->plasmaHunterWar = $plasmaHunterWar;
    }

    /**
     * @return mixed
     */
    public function getPointCorvet()
    {
        return $this->pointCorvet;
    }

    /**
     * @param mixed $pointCorvet
     */
    public function setPointCorvet($pointCorvet): void
    {
        $this->pointCorvet = $pointCorvet;
    }

    /**
     * @return mixed
     */
    public function getArmorCorvet()
    {
        return $this->armorCorvet;
    }

    /**
     * @param mixed $armorCorvet
     */
    public function setArmorCorvet($armorCorvet): void
    {
        $this->armorCorvet = $armorCorvet;
    }

    /**
     * @return mixed
     */
    public function getAccurateCorvet()
    {
        return $this->accurateCorvet;
    }

    /**
     * @param mixed $accurateCorvet
     */
    public function setAccurateCorvet($accurateCorvet): void
    {
        $this->accurateCorvet = $accurateCorvet;
    }

    /**
     * @return mixed
     */
    public function getMissileCorvet()
    {
        return $this->missileCorvet;
    }

    /**
     * @param mixed $missileCorvet
     */
    public function setMissileCorvet($missileCorvet): void
    {
        $this->missileCorvet = $missileCorvet;
    }

    /**
     * @return mixed
     */
    public function getShieldCorvet()
    {
        return $this->shieldCorvet;
    }

    /**
     * @param mixed $shieldCorvet
     */
    public function setShieldCorvet($shieldCorvet): void
    {
        $this->shieldCorvet = $shieldCorvet;
    }

    /**
     * @return mixed
     */
    public function getPointCorvetLaser()
    {
        return $this->pointCorvetLaser;
    }

    /**
     * @param mixed $pointCorvetLaser
     */
    public function setPointCorvetLaser($pointCorvetLaser): void
    {
        $this->pointCorvetLaser = $pointCorvetLaser;
    }

    /**
     * @return mixed
     */
    public function getArmorCorvetLaser()
    {
        return $this->armorCorvetLaser;
    }

    /**
     * @param mixed $armorCorvetLaser
     */
    public function setArmorCorvetLaser($armorCorvetLaser): void
    {
        $this->armorCorvetLaser = $armorCorvetLaser;
    }

    /**
     * @return mixed
     */
    public function getAccurateCorvetLaser()
    {
        return $this->accurateCorvetLaser;
    }

    /**
     * @param mixed $accurateCorvetLaser
     */
    public function setAccurateCorvetLaser($accurateCorvetLaser): void
    {
        $this->accurateCorvetLaser = $accurateCorvetLaser;
    }

    /**
     * @return mixed
     */
    public function getMissileCorvetLaser()
    {
        return $this->missileCorvetLaser;
    }

    /**
     * @param mixed $missileCorvetLaser
     */
    public function setMissileCorvetLaser($missileCorvetLaser): void
    {
        $this->missileCorvetLaser = $missileCorvetLaser;
    }

    /**
     * @return mixed
     */
    public function getLaserCorvetLaser()
    {
        return $this->laserCorvetLaser;
    }

    /**
     * @param mixed $laserCorvetLaser
     */
    public function setLaserCorvetLaser($laserCorvetLaser): void
    {
        $this->laserCorvetLaser = $laserCorvetLaser;
    }

    /**
     * @return mixed
     */
    public function getShieldCorvetLaser()
    {
        return $this->shieldCorvetLaser;
    }

    /**
     * @param mixed $shieldCorvetLaser
     */
    public function setShieldCorvetLaser($shieldCorvetLaser): void
    {
        $this->shieldCorvetLaser = $shieldCorvetLaser;
    }

    /**
     * @return mixed
     */
    public function getPointCorvetWar()
    {
        return $this->pointCorvetWar;
    }

    /**
     * @param mixed $pointCorvetWar
     */
    public function setPointCorvetWar($pointCorvetWar): void
    {
        $this->pointCorvetWar = $pointCorvetWar;
    }

    /**
     * @return mixed
     */
    public function getArmorCorvetWar()
    {
        return $this->armorCorvetWar;
    }

    /**
     * @param mixed $armorCorvetWar
     */
    public function setArmorCorvetWar($armorCorvetWar): void
    {
        $this->armorCorvetWar = $armorCorvetWar;
    }

    /**
     * @return mixed
     */
    public function getAccurateCorvetWar()
    {
        return $this->accurateCorvetWar;
    }

    /**
     * @param mixed $accurateCorvetWar
     */
    public function setAccurateCorvetWar($accurateCorvetWar): void
    {
        $this->accurateCorvetWar = $accurateCorvetWar;
    }

    /**
     * @return mixed
     */
    public function getMissileCorvetWar()
    {
        return $this->missileCorvetWar;
    }

    /**
     * @param mixed $missileCorvetWar
     */
    public function setMissileCorvetWar($missileCorvetWar): void
    {
        $this->missileCorvetWar = $missileCorvetWar;
    }

    /**
     * @return mixed
     */
    public function getLaserCorvetWar()
    {
        return $this->laserCorvetWar;
    }

    /**
     * @param mixed $laserCorvetWar
     */
    public function setLaserCorvetWar($laserCorvetWar): void
    {
        $this->laserCorvetWar = $laserCorvetWar;
    }

    /**
     * @return mixed
     */
    public function getShieldCorvetWar()
    {
        return $this->shieldCorvetWar;
    }

    /**
     * @param mixed $shieldCorvetWar
     */
    public function setShieldCorvetWar($shieldCorvetWar): void
    {
        $this->shieldCorvetWar = $shieldCorvetWar;
    }

    /**
     * @return mixed
     */
    public function getPointFregate()
    {
        return $this->pointFregate;
    }

    /**
     * @param mixed $pointFregate
     */
    public function setPointFregate($pointFregate): void
    {
        $this->pointFregate = $pointFregate;
    }

    /**
     * @return mixed
     */
    public function getArmorFregate()
    {
        return $this->armorFregate;
    }

    /**
     * @param mixed $armorFregate
     */
    public function setArmorFregate($armorFregate): void
    {
        $this->armorFregate = $armorFregate;
    }

    /**
     * @return mixed
     */
    public function getAccurateFregate()
    {
        return $this->accurateFregate;
    }

    /**
     * @param mixed $accurateFregate
     */
    public function setAccurateFregate($accurateFregate): void
    {
        $this->accurateFregate = $accurateFregate;
    }

    /**
     * @return mixed
     */
    public function getMissileFregate()
    {
        return $this->missileFregate;
    }

    /**
     * @param mixed $missileFregate
     */
    public function setMissileFregate($missileFregate): void
    {
        $this->missileFregate = $missileFregate;
    }

    /**
     * @return mixed
     */
    public function getLaserFregate()
    {
        return $this->laserFregate;
    }

    /**
     * @param mixed $laserFregate
     */
    public function setLaserFregate($laserFregate): void
    {
        $this->laserFregate = $laserFregate;
    }

    /**
     * @return mixed
     */
    public function getShieldFregate()
    {
        return $this->shieldFregate;
    }

    /**
     * @param mixed $shieldFregate
     */
    public function setShieldFregate($shieldFregate): void
    {
        $this->shieldFregate = $shieldFregate;
    }

    /**
     * @return mixed
     */
    public function getPointFregatePlasma()
    {
        return $this->pointFregatePlasma;
    }

    /**
     * @param mixed $pointFregatePlasma
     */
    public function setPointFregatePlasma($pointFregatePlasma): void
    {
        $this->pointFregatePlasma = $pointFregatePlasma;
    }

    /**
     * @return mixed
     */
    public function getArmorFregatePlasma()
    {
        return $this->armorFregatePlasma;
    }

    /**
     * @param mixed $armorFregatePlasma
     */
    public function setArmorFregatePlasma($armorFregatePlasma): void
    {
        $this->armorFregatePlasma = $armorFregatePlasma;
    }

    /**
     * @return mixed
     */
    public function getAccurateFregatePlasma()
    {
        return $this->accurateFregatePlasma;
    }

    /**
     * @param mixed $accurateFregatePlasma
     */
    public function setAccurateFregatePlasma($accurateFregatePlasma): void
    {
        $this->accurateFregatePlasma = $accurateFregatePlasma;
    }

    /**
     * @return mixed
     */
    public function getMissileFregatePlasma()
    {
        return $this->missileFregatePlasma;
    }

    /**
     * @param mixed $missileFregatePlasma
     */
    public function setMissileFregatePlasma($missileFregatePlasma): void
    {
        $this->missileFregatePlasma = $missileFregatePlasma;
    }

    /**
     * @return mixed
     */
    public function getLaserFregatePlasma()
    {
        return $this->laserFregatePlasma;
    }

    /**
     * @param mixed $laserFregatePlasma
     */
    public function setLaserFregatePlasma($laserFregatePlasma): void
    {
        $this->laserFregatePlasma = $laserFregatePlasma;
    }

    /**
     * @return mixed
     */
    public function getShieldFregatePlasma()
    {
        return $this->shieldFregatePlasma;
    }

    /**
     * @param mixed $shieldFregatePlasma
     */
    public function setShieldFregatePlasma($shieldFregatePlasma): void
    {
        $this->shieldFregatePlasma = $shieldFregatePlasma;
    }

    /**
     * @return mixed
     */
    public function getPlasmaFregatePlasma()
    {
        return $this->plasmaFregatePlasma;
    }

    /**
     * @param mixed $plasmaFregatePlasma
     */
    public function setPlasmaFregatePlasma($plasmaFregatePlasma): void
    {
        $this->plasmaFregatePlasma = $plasmaFregatePlasma;
    }

    /**
     * @return mixed
     */
    public function getPointCroiser()
    {
        return $this->pointCroiser;
    }

    /**
     * @param mixed $pointCroiser
     */
    public function setPointCroiser($pointCroiser): void
    {
        $this->pointCroiser = $pointCroiser;
    }

    /**
     * @return mixed
     */
    public function getArmorCroiser()
    {
        return $this->armorCroiser;
    }

    /**
     * @param mixed $armorCroiser
     */
    public function setArmorCroiser($armorCroiser): void
    {
        $this->armorCroiser = $armorCroiser;
    }

    /**
     * @return mixed
     */
    public function getAccurateCroiser()
    {
        return $this->accurateCroiser;
    }

    /**
     * @param mixed $accurateCroiser
     */
    public function setAccurateCroiser($accurateCroiser): void
    {
        $this->accurateCroiser = $accurateCroiser;
    }

    /**
     * @return mixed
     */
    public function getMissileCroiser()
    {
        return $this->missileCroiser;
    }

    /**
     * @param mixed $missileCroiser
     */
    public function setMissileCroiser($missileCroiser): void
    {
        $this->missileCroiser = $missileCroiser;
    }

    /**
     * @return mixed
     */
    public function getLaserCroiser()
    {
        return $this->laserCroiser;
    }

    /**
     * @param mixed $laserCroiser
     */
    public function setLaserCroiser($laserCroiser): void
    {
        $this->laserCroiser = $laserCroiser;
    }

    /**
     * @return mixed
     */
    public function getShieldCroiser()
    {
        return $this->shieldCroiser;
    }

    /**
     * @param mixed $shieldCroiser
     */
    public function setShieldCroiser($shieldCroiser): void
    {
        $this->shieldCroiser = $shieldCroiser;
    }

    /**
     * @return mixed
     */
    public function getPlasmaCroiser()
    {
        return $this->plasmaCroiser;
    }

    /**
     * @param mixed $plasmaCroiser
     */
    public function setPlasmaCroiser($plasmaCroiser): void
    {
        $this->plasmaCroiser = $plasmaCroiser;
    }

    /**
     * @return mixed
     */
    public function getPointIronClad()
    {
        return $this->pointIronClad;
    }

    /**
     * @param mixed $pointIronClad
     */
    public function setPointIronClad($pointIronClad): void
    {
        $this->pointIronClad = $pointIronClad;
    }

    /**
     * @return mixed
     */
    public function getArmorIronClad()
    {
        return $this->armorIronClad;
    }

    /**
     * @param mixed $armorIronClad
     */
    public function setArmorIronClad($armorIronClad): void
    {
        $this->armorIronClad = $armorIronClad;
    }

    /**
     * @return mixed
     */
    public function getAccurateIronClad()
    {
        return $this->accurateIronClad;
    }

    /**
     * @param mixed $accurateIronClad
     */
    public function setAccurateIronClad($accurateIronClad): void
    {
        $this->accurateIronClad = $accurateIronClad;
    }

    /**
     * @return mixed
     */
    public function getMissileIronClad()
    {
        return $this->missileIronClad;
    }

    /**
     * @param mixed $missileIronClad
     */
    public function setMissileIronClad($missileIronClad): void
    {
        $this->missileIronClad = $missileIronClad;
    }

    /**
     * @return mixed
     */
    public function getLaserIronClad()
    {
        return $this->laserIronClad;
    }

    /**
     * @param mixed $laserIronClad
     */
    public function setLaserIronClad($laserIronClad): void
    {
        $this->laserIronClad = $laserIronClad;
    }

    /**
     * @return mixed
     */
    public function getShieldIronClad()
    {
        return $this->shieldIronClad;
    }

    /**
     * @param mixed $shieldIronClad
     */
    public function setShieldIronClad($shieldIronClad): void
    {
        $this->shieldIronClad = $shieldIronClad;
    }

    /**
     * @return mixed
     */
    public function getPlasmaIronClad()
    {
        return $this->plasmaIronClad;
    }

    /**
     * @param mixed $plasmaIronClad
     */
    public function setPlasmaIronClad($plasmaIronClad): void
    {
        $this->plasmaIronClad = $plasmaIronClad;
    }

    /**
     * @return mixed
     */
    public function getPointDestroyer()
    {
        return $this->pointDestroyer;
    }

    /**
     * @param mixed $pointDestroyer
     */
    public function setPointDestroyer($pointDestroyer): void
    {
        $this->pointDestroyer = $pointDestroyer;
    }

    /**
     * @return mixed
     */
    public function getArmorDestroyer()
    {
        return $this->armorDestroyer;
    }

    /**
     * @param mixed $armorDestroyer
     */
    public function setArmorDestroyer($armorDestroyer): void
    {
        $this->armorDestroyer = $armorDestroyer;
    }

    /**
     * @return mixed
     */
    public function getAccurateDestroyer()
    {
        return $this->accurateDestroyer;
    }

    /**
     * @param mixed $accurateDestroyer
     */
    public function setAccurateDestroyer($accurateDestroyer): void
    {
        $this->accurateDestroyer = $accurateDestroyer;
    }

    /**
     * @return mixed
     */
    public function getMissileDestroyer()
    {
        return $this->missileDestroyer;
    }

    /**
     * @param mixed $missileDestroyer
     */
    public function setMissileDestroyer($missileDestroyer): void
    {
        $this->missileDestroyer = $missileDestroyer;
    }

    /**
     * @return mixed
     */
    public function getLaserDestroyer()
    {
        return $this->laserDestroyer;
    }

    /**
     * @param mixed $laserDestroyer
     */
    public function setLaserDestroyer($laserDestroyer): void
    {
        $this->laserDestroyer = $laserDestroyer;
    }

    /**
     * @return mixed
     */
    public function getShieldDestroyer()
    {
        return $this->shieldDestroyer;
    }

    /**
     * @param mixed $shieldDestroyer
     */
    public function setShieldDestroyer($shieldDestroyer): void
    {
        $this->shieldDestroyer = $shieldDestroyer;
    }

    /**
     * @return mixed
     */
    public function getPlasmaDestroyer()
    {
        return $this->plasmaDestroyer;
    }

    /**
     * @param mixed $plasmaDestroyer
     */
    public function setPlasmaDestroyer($plasmaDestroyer): void
    {
        $this->plasmaDestroyer = $plasmaDestroyer;
    }

    /**
     * @return mixed
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * @param mixed $lastUpdate
     */
    public function setLastUpdate($lastUpdate): void
    {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param mixed $max
     */
    public function setMax($max): void
    {
        $this->max = $max;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getArmorPoints(): int
    {
        return $this->armorHunter + $this->armorHunterHeavy + $this->armorHunterWar + $this->armorCorvet + $this->armorCorvetLaser + $this->armorCorvetWar + $this->armorFregate + $this->armorFregatePlasma + $this->armorCroiser + $this->armorIronClad + $this->armorDestroyer;
    }

    /**
     * @return int
     */
    public function getShieldPoints(): int
    {
        return $this->shieldCorvet + $this->shieldCorvetWar + $this->shieldFregate + $this->shieldFregatePlasma + $this->shieldCroiser + $this->shieldIronClad + $this->shieldDestroyer;
    }

    /**
     * @return int
     */
    public function getMissilePoints(): int
    {
        return$this->missileHunter + $this->missileHunterHeavy + $this->missileHunterWar + $this->missileCorvet + $this->missileCorvetLaser + $this->missileCorvetWar + $this->missileFregate + $this->missileFregatePlasma + $this->missileCroiser + $this->missileIronClad + $this->missileDestroyer;
    }

    /**
     * @return int
     */
    public function getLaserPoints(): int
    {
        return $this->laserHunterWar + $this->laserCorvetLaser + $this->laserCorvetWar + $this->laserFregate + $this->laserFregatePlasma + $this->laserCroiser + $this->laserIronClad + $this->laserDestroyer;
    }

    /**
     * @return int
     */
    public function getPlasmaPoints(): int
    {
        return $this->plasmaHunterWar + $this->plasmaFregatePlasma + $this->plasmaCroiser + $this->plasmaIronClad + $this->plasmaDestroyer;
    }

    /**
     * @return int
     */
    public function getPrecisionPoints(): int
    {
        return $this->accurateHunter + $this->accurateHunterHeavy + $this->accurateHunterWar + $this->accurateCorvet + $this->accurateCorvetLaser + $this->accurateCorvetWar + $this->accurateFregate + $this->accurateFregatePlasma + $this->accurateCroiser + $this->accurateIronClad + $this->accurateDestroyer;
    }

    /**
     * @return int
     */
    public function getRemainingPoints(): int
    {
        return $this->pointHunter + $this->pointHunterHeavy + $this->pointHunterWar + $this->pointCorvet + $this->pointCorvetLaser + $this->pointCorvetWar + $this->pointFregate + $this->pointFregatePlasma + $this->pointCroiser + $this->pointIronClad + $this->pointDestroyer;
    }

    /**
     * @return int
     */
    public function getRetry(): int
    {
        return $this->retry;
    }

    /**
     * @param int $retry
     */
    public function setRetry(int $retry): void
    {
        $this->retry = $retry;
    }
}
