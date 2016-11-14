<?php

/**
 *  This file is part of AMPortal, released under GNU/GPLv3
 *  See LICENSE or go to <http://www.gnu.org/licenses/> for details.
 *  Copyright (C) 2016  Adrien Mahieux
 */

namespace AMPortal\TechInventory\Models;


class Cpu extends BaseModel {

	private $id;

	public $manufacturer;
	public $manufacturerId;
	public $name;
	public $model;
	public $code;
	public $sockets;
	public $lithography;
	public $nbCores;
	public $nbThreads;
	public $freqBase;
	public $freqMax;
	public $freqBus;
	public $memTypes;
	public $memMaxSize;
	public $memMaxChans;
	public $memMaxBw;
	public $cache1Size;
	public $cache2Size;
	public $cache3Size;
	public $pcieVersion;
	public $pcieLanes;
	public $pcieConf;
	public $pciVersion;
	public $tdp;
	public $supportStatus;
	public $supportDateEol;
	public $dateLaunch;
	public $priceMin;
	public $priceMax;

}