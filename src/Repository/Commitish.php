<?php

declare( strict_types=1 );

namespace GitZenith\Repository;

use GitZenith\Repository;
use GitZenith\SCM\Branch;
use GitZenith\SCM\Exception\InvalidCommitException;
use GitZenith\SCM\Tag;

/**
 * Resolves the ref/path syntax used by GitZenith URLs.
 *
 * The ref is kept for URL/navigation purposes; the hash is always the full
 * commit ID used for Git object reads. A path is null when the URL points at
 * the root of the selected commit.
 */
class Commitish
{
	protected string $hash;
	protected string $ref;
	protected ?string $path = null;
	protected string $type;

	public function __construct( Repository $repository, string $commitish )
	{
		$commitish = trim( $commitish, '/' );
		if( $commitish === '' )
		{
			$commitish = 'HEAD';
		}

		$parts = explode( '/', $commitish );
		$branches = [];
		foreach( $repository->getBranches() as $branch )
		{
			if( $branch instanceof Branch )
			{
				$branches[$branch->getName()] = true;
			}
		}

		$tags = [];
		foreach( $repository->getTags() as $tag )
		{
			if( $tag instanceof Tag )
			{
				$tags[$tag->getName()] = true;
			}
		}

		$resolvedRef = null;
		$resolvedType = null;
		$resolvedPath = null;

		// Longest matching ref wins. This supports slash-containing branch and tag names.
		for( $length = count( $parts ); $length >= 1; --$length )
		{
			$candidate = implode( '/', array_slice( $parts, 0, $length ) );
			$type = null;

			// Explicit branch > tag > object ID precedence makes branch/tag name
			// collisions deterministic and matches the usual Git web browsing expectation.
			if( isset( $branches[$candidate] ) )
			{
				$type = 'branch';
			}
			elseif( isset( $tags[$candidate] ) )
			{
				$type = 'tag';
			}
			elseif( $candidate === 'HEAD' )
			{
				$type = 'revision';
			}
			elseif( $repository->isValidCommitId( $candidate ) )
			{
				$type = 'commit';
			}

			if( $type !== null )
			{
				$resolvedRef = $candidate;
				$resolvedType = $type;
				$remaining = implode( '/', array_slice( $parts, $length ) );
				$resolvedPath = $remaining === '' ? null : $remaining;
				break;
			}
		}

		if( $resolvedRef === null || $resolvedType === null )
		{
			throw new InvalidCommitException( sprintf( 'Unable to resolve Git ref in "%s".', $commitish ) );
		}

		try
		{
			$this->hash = $repository->resolveCommitHash( $resolvedRef, $resolvedType );
		}
		catch( \Throwable $exception )
		{
			throw new InvalidCommitException( sprintf( 'Unable to resolve Git ref "%s" to a commit.', $resolvedRef ), 0, $exception );
		}

		$this->ref = $resolvedRef;
		$this->type = $resolvedType;
		$this->path = $resolvedPath;
	}

	/** Return the full commit hash resolved from the ref. */
	public function getHash(): string
	{
		return $this->hash;
	}

	/** Return the original ref used in the URL. */
	public function getRef(): string
	{
		return $this->ref;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function hasPath(): bool
	{
		return $this->path !== null && $this->path !== '';
	}

	public function getPath(): ?string
	{
		return $this->path;
	}
}
