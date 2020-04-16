<?php declare( strict_types=1 );

class WW_WwTest_IdsSpeedTest_UseCase
{
	/** @var string */
	private $name;

	/** @var string */
	private $description;

	/** @var int */
	private $articleId;

	/** @var int */
	private $layoutId;

	/** @var int */
	private $editionId;

	public function __construct( string $name, string $description, int $articleId, int $layoutId, int $editionId )
	{
		$this->name = $name;
		$this->description = $description;
		$this->articleId = $articleId;
		$this->layoutId = $layoutId;
		$this->editionId = $editionId;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getDescription(): string
	{
		return $this->description;
	}

	public function getArticleId(): int
	{
		return $this->articleId;
	}

	public function getLayoutId(): int
	{
		return $this->layoutId;
	}

	public function getEditionId(): int
	{
		return $this->editionId;
	}
}