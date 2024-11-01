<?php

declare( strict_types=1 );

namespace GitZenith\SCM;

class Repository
{
	public function __construct( protected string $path )
	{
	}

	public function getPath(): string
	{
		return $this->path;
	}
}
