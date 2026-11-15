<?php

declare( strict_types=1 );

namespace GitZenith\Repository;

use Carbon\Carbon;
use GitZenith\Repository;
use GitZenith\SCM\Branch;
use GitZenith\SCM\Commit;
use GitZenith\SCM\Commit\Person;
use GitZenith\SCM\Repository as SourceRepository;
use GitZenith\SCM\Tag;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class CommitishTest extends TestCase
{
	use ProphecyTrait;

	private ObjectProphecy $repository;

	protected function setUp(): void
	{
		parent::setUp();

		$this->repository = $this->prophesize( Repository::class );

		$this->repository->getBranches()->willReturn( $this->getFixtureBranches() );

		$this->repository->getTags()->willReturn( $this->getFixtureTags() );

		$this->repository->isValidCommitId( Argument::type( 'string' ) )
			->will( static function( array $args ): bool {
				return preg_match( '/^[a-f0-9]{40}$/i', $args[0] ) === 1;
			}
		);

		$this->repository->resolveCommitHash(
			Argument::type( 'string' ),
			Argument::type( 'string' )
		)->will( static function( array $args ): string {
			return $args[0];
			}
		);
	}

	public function testIsDetectingBranch(): void
	{
		$repository = $this->repository;
		// $repository = $this->prophesize( Repository::class );
		// $repository->getBranches()->willReturn( $this->getFixtureBranches() );
		// $repository->getTags()->willReturn( $this->getFixtureTags() );
		// $repository->isValidCommitId( Argument::type( 'string' ) )->willReturn( false );

		$commitish = new Commitish( $repository->reveal(), 'bugfix/bar/test/file.php' );
		$this->assertEquals( 'bugfix/bar', $commitish->getHash() );
		$this->assertEquals( 'test/file.php', $commitish->getPath() );

		$commitish = new Commitish( $repository->reveal(), 'feature/test/foo/test/file.php' );
		$this->assertEquals( 'feature/test/foo', $commitish->getHash() );
		$this->assertEquals( 'test/file.php', $commitish->getPath() );
	}

	public function testIsDetectingTag(): void
	{
		$repository = $this->repository;
		// $repository = $this->prophesize( Repository::class );
		// $repository->getBranches()->willReturn( $this->getFixtureBranches() );
		// $repository->getTags()->willReturn( $this->getFixtureTags() );
		// $repository->isValidCommitId( Argument::type( 'string' ) )->willReturn( false );

		$commitish = new Commitish( $repository->reveal(), 'v1.2-test/test/file.php' );
		$this->assertEquals( 'v1.2-test', $commitish->getHash() );
		$this->assertEquals( 'test/file.php', $commitish->getPath() );

		$commitish = new Commitish( $repository->reveal(), 'v2.0/ab/test/file.php' );
		$this->assertEquals( 'v2.0/ab', $commitish->getHash() );
		$this->assertEquals( 'test/file.php', $commitish->getPath() );
	}

	public function testIsDetectingCommit(): void
	{
		$repository = $this->repository;
		// $repository = $this->prophesize( Repository::class );
		// $repository->getBranches()->willReturn( $this->getFixtureBranches() );
		// $repository->getTags()->willReturn( $this->getFixtureTags() );
		// $repository->isValidCommitId( Argument::type( 'string' ) )->willReturn( false );

		$commitish = new Commitish( $repository->reveal(), '14f8d0b69fa61d2d6daa2acc5b38c9956973206b/test/file.php' );
		$this->assertEquals( '14f8d0b69fa61d2d6daa2acc5b38c9956973206b', $commitish->getHash() );
		$this->assertEquals( 'test/file.php', $commitish->getPath() );
	}

	public function testIsDetectingRefWithDate(): void
	{
		$repository = $this->repository;
		// $repository = $this->prophesize( Repository::class );
		// $repository->getBranches()->willReturn( $this->getFixtureBranches() );
		// $repository->getTags()->willReturn( $this->getFixtureTags() );
		// $repository->isValidCommitId( Argument::type( 'string' ) )->willReturn( false );

		$commitish = new Commitish( $repository->reveal(), 'bugfix/bar@{yesterday}/test/file.php' );
		$this->assertEquals( 'bugfix/bar@{yesterday}', $commitish->getRef() );
		$this->assertEquals( 'test/file.php', $commitish->getPath() );
	}

	public function testIsDetectingRefWithNumber(): void
	{
		$repository = $this->repository;
		// $repository = $this->prophesize( Repository::class );
		// $repository->getBranches()->willReturn( $this->getFixtureBranches() );
		// $repository->getTags()->willReturn( $this->getFixtureTags() );
		// $repository->isValidCommitId( Argument::type( 'string' ) )->willReturn( false );

		$commitish = new Commitish( $repository->reveal(), 'bugfix/bar~3/test/file.php' );
		$this->assertEquals( 'bugfix/bar~3', $commitish->getRef() );
		$this->assertEquals( 'test/file.php', $commitish->getPath() );

		$commitish = new Commitish( $repository->reveal(), 'bugfix/bar^{14f8d0b69fa61d2d6daa2acc5b38c9956973206b}/test/file.php' );
		$this->assertEquals( 'bugfix/bar^{14f8d0b69fa61d2d6daa2acc5b38c9956973206b}', $commitish->getHash() );
		$this->assertEquals( 'test/file.php', $commitish->getPath() );
	}

	protected function getFixtureBranches(): array
	{
		$sourceRepository = new SourceRepository( '/repo' );

		return [
			new Branch( $sourceRepository, 'foo', new Commit( $sourceRepository, 'foo' ) ),
			new Branch( $sourceRepository, 'bugfix/bar', new Commit( $sourceRepository, 'bugfix/bar' ) ),
			new Branch( $sourceRepository, 'feature/test/foo', new Commit( $sourceRepository, 'feature/test/foo' ) ),
		];
	}

	protected function getFixtureTags(): array
	{
		$sourceRepository = new SourceRepository( '/repo' );
		$author = new Person( 'Foo', 'foo@bar.com' );
		$authoredAt = new Carbon( '1990-01-22 09:00:00' );

		return [
			new Tag( $sourceRepository, 'v1.2', $author, $authoredAt ),
			new Tag( $sourceRepository, 'v1.2-test', $author, $authoredAt ),
			new Tag( $sourceRepository, 'v2.0/ab', $author, $authoredAt ),
		];
	}
}
