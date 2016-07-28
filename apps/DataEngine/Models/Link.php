<?php

namespace AMPortal\DataEngine\Models;

class Link extends BaseModel {

	public $name;

	/**
	 * @Primary
	 * @Column(type="integer", nullable=false)
	 */
	public $idPlaceholderSrc;

	/**
	 * @Primary
	 * @Column(type="integer", nullable=false)
	 */
	public $idPlaceholderDst;

	public $idFieldSrc0;
	public $idFieldSrc1;
	public $idFieldSrc2;
	public $idFieldSrc3;
	public $idFieldSrc4;
	public $idFieldSrc5;
	
	public $idFieldDst0;
	public $idFieldDst1;
	public $idFieldDst2;
	public $idFieldDst3;
	public $idFieldDst4;
	public $idFieldDst5;


	public $linkType;

	

}