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

		// Find the longest matching branch or tag name.
		$refs = [];

		foreach( $branches as $name => $_ )
		{
			$refs[$name] = 'branch';
		}

		foreach( $tags as $name => $_ )
		{
			$refs[$name] ??= 'tag';
		}

		uksort( $refs, static fn( $a, $b ) => strlen( $b ) <=> strlen( $a ) );

		foreach( $refs as $name => $type )
		{
			if( !str_starts_with( $commitish, $name ) )
			{
				continue;
			}

			$remaining = substr( $commitish, strlen( $name ) );

			// The ref must end here, or be followed by a revision operator or path separator.
			if( $remaining !== '' && !preg_match( '/^(?:~|\^|@\{|\/)/', $remaining ) )
			{
				continue;
			}

			$resolvedRef = $name;
			$resolvedType = $type;

			// Check whether a Git revision expression follows the ref.
			if( preg_match( '/^((?:\^\{[^}]+\}|@\{[^}]+\}|~\d*|\^\d*)+)/', $remaining, $matches ) )
			{
				$resolvedRef .= $matches[1];
				$remaining = substr( $remaining, strlen( $matches[1] ) );
			}

			$remaining = ltrim( $remaining, '/' );
			$resolvedPath = $remaining === '' ? null : $remaining;

			break;
		}

		// HEAD and commit IDs are handled separately.
		if( $resolvedRef === null )
		{
			if( $commitish === 'HEAD' || str_starts_with( $commitish, 'HEAD/' ) )
			{
				$resolvedRef = 'HEAD';
				$resolvedType = 'revision';
				$remaining = substr( $commitish, strlen( 'HEAD' ) );
				$remaining = ltrim( $remaining, '/' );
				$resolvedPath = $remaining === '' ? null : $remaining;
			}
			else
			{
				$parts = explode( '/', $commitish, 2 );
				$candidate = $parts[0];

				if( $repository->isValidCommitId( $candidate ) )
				{
					$resolvedRef = $candidate;
					$resolvedType = 'commit';
					$remaining = $parts[1] ?? '';
					$resolvedPath = $remaining === '' ? null : $remaining;
				}
			}
		}

		if( $resolvedRef === null || $resolvedType === null )
		{
			throw new InvalidCommitException(
				sprintf( 'Unable to resolve Git ref "%s" to a commit.', $commitish )
			);
		}

		try
		{
			$this->hash = $repository->resolveCommitHash( $resolvedRef, $resolvedType );
		}
		catch( \Throwable $exception )
		{
			throw new InvalidCommitException(
				sprintf( 'Unable to resolve Git ref "%s" to a commit.', $resolvedRef ),
				0,
				$exception
			);
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
