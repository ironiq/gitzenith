<?php

declare( strict_types=1 );

namespace GitZenith;

use GitZenith\SCM\Blame;
use GitZenith\SCM\Blob;
use GitZenith\SCM\Branch;
use GitZenith\SCM\Commit;
use GitZenith\SCM\Commit\Criteria;
use GitZenith\SCM\Repository as SourceRepository;
use GitZenith\SCM\System;
use GitZenith\SCM\Tree;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class RepositoryTest extends TestCase
{
	use ProphecyTrait;

	private const TEST_COMMIT_HASH = '1234567890123456789012345678901234567890';

	private function createSystemMock( SourceRepository $repository ): ObjectProphecy
	{
		$system = $this->prophesize( System::class );

		$system->getBranches( $repository )->willReturn( [] );
		$system->getTags( $repository )->willReturn( [] );
		$system->isValidCommitId( $repository, Argument::type( 'string' ) )->willReturn( false );
		$system->getCommits( $repository, Argument::cetera() )->willReturn( [] );

		return $system;
	}

	private function createBranch( SourceRepository $repository, string $name ): Branch
	{
		return new Branch(
			$repository,
			$name,
			new Commit( $repository, self::TEST_COMMIT_HASH )
		);
	}

	public function testIsGettingBranches(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->getBranches( $sourceRepository )->shouldBeCalled()->willReturn( [] );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getBranches();
	}

	public function testIsGettingTags(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->getTags( $sourceRepository )->shouldBeCalled()->willReturn( [] );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getTags();
	}

	public function testIsGettingTreeWithHash(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->isValidCommitId( $sourceRepository, self::TEST_COMMIT_HASH )->willReturn( true );
		$system->resolveCommitHash( $sourceRepository, self::TEST_COMMIT_HASH, 'commit' )->willReturn( self::TEST_COMMIT_HASH );
		$system->getTree( $sourceRepository, self::TEST_COMMIT_HASH )->shouldBeCalled()->willReturn( new Tree( $sourceRepository, self::TEST_COMMIT_HASH ) );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getTree( self::TEST_COMMIT_HASH );
	}

	public function testIsGettingTreeWithoutHash(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->getTree( $sourceRepository )->shouldBeCalled()->willReturn( new Tree( $sourceRepository, '123' ) );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getTree();
	}

	public function testIsGettingPathTreeWithHash(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->isValidCommitId( $sourceRepository, self::TEST_COMMIT_HASH )->willReturn( true );
		$system->resolveCommitHash( $sourceRepository, self::TEST_COMMIT_HASH, 'commit' )->willReturn( self::TEST_COMMIT_HASH );
		$system->getPathTree( $sourceRepository, 'f/b', self::TEST_COMMIT_HASH )->shouldBeCalled()->willReturn( new Tree( $sourceRepository, '123' ) );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getTree( self::TEST_COMMIT_HASH . '/f/b' );
	}

	public function testIsGettingCommitWithHash(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->isValidCommitId( $sourceRepository, self::TEST_COMMIT_HASH )->willReturn( true );
		$system->resolveCommitHash( $sourceRepository, self::TEST_COMMIT_HASH, 'commit' )->willReturn( self::TEST_COMMIT_HASH );
		$system->getCommit( $sourceRepository, self::TEST_COMMIT_HASH )->shouldBeCalled()->willReturn( new Commit( $sourceRepository, self::TEST_COMMIT_HASH ) );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getCommit( self::TEST_COMMIT_HASH );
	}

	public function testIsGettingCommitWithoutHash(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->getCommit( $sourceRepository )->shouldBeCalled()->willReturn( new Commit( $sourceRepository, '123' ) );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getCommit();
	}

	public function testIsGettingCommitsWithHash(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->isValidCommitId( $sourceRepository, self::TEST_COMMIT_HASH )->willReturn( true );
		$system->resolveCommitHash( $sourceRepository, self::TEST_COMMIT_HASH, 'commit' )->willReturn( self::TEST_COMMIT_HASH );
		$system->getCommits( $sourceRepository, self::TEST_COMMIT_HASH, 1, 10 )->shouldBeCalled()->willReturn( [] );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getCommits( self::TEST_COMMIT_HASH, 1, 10 );
	}

	public function testIsGettingCommitsWithoutHash(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->getCommits( $sourceRepository, 'HEAD', 1, 10 )->shouldBeCalled()->willReturn( [] );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getCommits( null, 1, 10 );
	}

	public function testIsGettingSpecificCommits(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->getSpecificCommits( $sourceRepository, ['a', 'b'] )->shouldBeCalled()->willReturn( [] );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getSpecificCommits( ['a', 'b'] );
	}

	public function testIsGettingBlame(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->isValidCommitId( $sourceRepository, self::TEST_COMMIT_HASH )->willReturn( true );
		$system->resolveCommitHash( $sourceRepository, self::TEST_COMMIT_HASH, 'commit' )->willReturn( self::TEST_COMMIT_HASH );
		$system->getBlame( $sourceRepository, self::TEST_COMMIT_HASH, 'foo.php' )->shouldBeCalled()->willReturn( new Blame( self::TEST_COMMIT_HASH, 'foo.php' ) );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getBlame( self::TEST_COMMIT_HASH . '/foo.php' );
	}

	public function testIsGettingBlob(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->getBranches( $sourceRepository )
			->willReturn( [ $this->createBranch( $sourceRepository, 'master' ) ] );
		$system->resolveCommitHash( $sourceRepository, 'master', 'branch' )
			->willReturn( self::TEST_COMMIT_HASH );

		$system->getBlob( $sourceRepository, self::TEST_COMMIT_HASH, 'test.c' )
			->shouldBeCalled()
			->willReturn( new Blob( $sourceRepository, 'test.c' ) );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->getBlob( 'master/test.c' );
	}

	public function testIsSearchingCommits(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );
		$criteria = new Criteria();

		$system = $this->createSystemMock( $sourceRepository );
		$system->searchCommits( $sourceRepository, $criteria )->shouldBeCalled()->willReturn( [] );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->searchCommits( $criteria );
	}

	public function testIsSearchingCommitsWithCommitish(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );
		$criteria = new Criteria();

		$system = $this->createSystemMock( $sourceRepository );
		$system->getBranches( $sourceRepository )->willReturn( [ $this->createBranch( $sourceRepository, 'master' ) ] );
		$system->resolveCommitHash( $sourceRepository, 'master', 'branch' )->willReturn( self::TEST_COMMIT_HASH );
		$system->searchCommits( $sourceRepository, $criteria, self::TEST_COMMIT_HASH )->shouldBeCalled()->willReturn( [] );
		// $system->searchCommits( $sourceRepository, $criteria, self::TEST_COMMIT_HASH )->shouldBeCalled()->willReturn( [] );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->searchCommits( $criteria, 'master' );
	}

	public function testIsArchiving(): void
	{
		$sourceRepository = new SourceRepository( '/foo/bar' );

		$system = $this->createSystemMock( $sourceRepository );
		$system->getBranches( $sourceRepository )->willReturn( [ $this->createBranch( $sourceRepository, 'master' ) ] );
		$system->resolveCommitHash( $sourceRepository, 'master', 'branch' )->willReturn( self::TEST_COMMIT_HASH );
		$system->archive( $sourceRepository, 'zip', self::TEST_COMMIT_HASH, 'test.c' )->shouldBeCalled()->willReturn( '/tmp/test.zip' );

		$repository = new Repository( $system->reveal(), $sourceRepository, 'bar' );
		$repository->archive( 'zip', 'master/test.c' );
	}
}
