<?php

declare( strict_types=1 );

namespace GitZenith\Repository;

use GitZenith\Repository;

class Commitish
{
	protected string $hash;
	protected ?string $path = null;

	public function __construct( Repository $repository, string $commitish )
	{
		// Reworked branch/tag/commithash handling
		// It seems this class is called several ways with the following possible params:
		// - branchname
		// - branchname/path
		// - tag
		// - tag/path
		// - commithash
		// - commithash/path

		$found = null;
		$pathInTree = '.';
		$parts = explode( '/', $commitish );
		for( $i = count( $parts ); $i >= 1; --$i )
		{
			$candidate = implode( '/', array_slice( $parts, 0, $i ) );
			$candidate = trim( $candidate, '/' );

			if( $repository->isValidBranch( $candidate ) )
			{
				$type = 'branch';
				$found = true;
			}
			elseif( $repository->isValidGitTag( $candidate, $repository ) )
			{
				$type = 'tag';
				$found = true;
			}
			elseif( $repository->isValidCommitId( $candidate, $repository ) )
			{
				$type = 'commit';
				$found = true;
			}
			else
			{
				$found = false;
			}

			if( $found )
			{
				$pathInTree = implode( '/', array_slice( $parts, $i ) );
				$pathInTree = ( $pathInTree !== '' ) ? $pathInTree : '.';
				break;
			}
		}

		if( $found )
		{
			switch( $type )
			{
				case 'branch':
					$this->hash = $repository->getLastCommit()->getHash();
					if( $repository->getCurrentBranch() !== $candidate )
					{
						$repository->setCurrentBranch( $candidate );
					}
					break;

				case 'tag':
					// code...
					break;

				case 'commit':
					$this->hash = $candidate;
					break;
			}
			$this->path = $pathInTree;
		}
		else
		{
			$foo = $repository->getCommitsFromPath( $pathInTree, 'HEAD', 1, 1 );
			if( is_array( $foo ) )
			{
				$this->hash = array_values( $foo )[0]->getHash();
			}
			else
			{
				$this->hash = $foo;
			}
			$this->path = $pathInTree;
		}
	}

	public function getHash(): string
	{
		return $this->hash;
	}

	public function hasPath(): bool
	{
		return (bool) $this->path;
	}

	public function getPath(): ?string
	{
		return $this->path;
	}
}
