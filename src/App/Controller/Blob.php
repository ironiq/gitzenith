<?php

declare( strict_types=1 );

namespace GitZenith\App\Controller;

use GitZenith\Repository\Index;
use GitZenith\SCM\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class Blob
{
	public function __construct( protected Environment $templating, protected Index $index, protected int $perPage )
	{
	}

	#[Route(
		'/{repo}/blob/{commitish}',
		name: 'blob_show',
		requirements: [ 'repo' => '%valid_repository_name%', 'commitish' => '%valid_commitish_format%' ],
	)]
	public function show( string $repo, string $commitish ): Response
	{
		$arr = explode( '/', $commitish );
		$commitid = $arr[0];

		$repository = $this->index->getRepository( $repo );
		$curbranch = $repository->getCurrentBranch();

		$branches = [];
		foreach( $repository->getBranches() as $b )
		{
			$branches[] = $b->getName();
		}
		if( in_array( $commitid, $branches ) && $commitid !== $curbranch )
		{
			$repository->setCurrentBranch( $repository, $commitid );
			$curbranch = $repository->getCurrentBranch();
		}

		$origcommit = $this->index->getSystem( $repository->getRepository() )->getCommit( $repository->getRepository(), $commitid );
		$blob = $repository->getBlob( $commitish );
		$commit = $repository->getCommit( $blob->getHash() );
		$file = File::createFromBlob( $blob );

		if( $commitid === 'HEAD' || $commitid === $curbranch )
		{
			$shortref = $curbranch;
			$longref = $shortref;
		}
		else
		{
			$shortref = $origcommit->getShortHash();
			$longref = $origcommit->getHash();
		}

		if( $file->isBinary() )
		{
			$response = new Response( $file->getContents() );
			$disposition = $response->headers->makeDisposition( ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getName() );
			$response->headers->set( 'Content-Disposition', $disposition );
			$response->headers->set( 'Content-Type', $file->getMimeType() );

			return $response;
		}

		return new Response( $this->templating->render( 'Blob/show.html.twig', [
			'repository' => $repository,
			'commit' => $commit,
			'blob' => $blob,
			'file' => $file,
			'shortref' => $shortref,
			'longref' => $longref,
			'ref' => $repository->getCurrentBranch(),
		] ) );
	}

	#[Route(
		'/{repo}/raw/{commitish}',
		name: 'blob_raw',
		requirements: [ 'repo' => '%valid_repository_name%', 'commitish' => '%valid_commitish_format%' ],
	)]
	public function showRaw( string $repo, string $commitish ): Response
	{
		$repository = $this->index->getRepository( $repo );
		$blob = $repository->getBlob( $commitish );
		$file = File::createFromBlob( $blob );

		$response = new Response( $file->getContents() );
		$response->headers->set( 'Content-Type', $file->getMimeType() );

		if( $file->isBinary() )
		{
			$disposition = $response->headers->makeDisposition( ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getName() );
			$response->headers->set( 'Content-Disposition', $disposition );
		}

		return $response;
	}

	#[Route(
		'/{repo}/blame/{commitish}',
		name: 'blob_blame',
		requirements: [ 'repo' => '%valid_repository_name%', 'commitish' => '%valid_commitish_format%' ],
	)]
	public function blame( string $repo, string $commitish ): Response
	{
		$repository = $this->index->getRepository( $repo );
		$blob = $repository->getBlob( $commitish );
		$blame = $repository->getBlame( $commitish );

		return new Response( $this->templating->render( 'Blob/blame.html.twig', [
			'repository' => $repository,
			'blame' => $blame,
			'blob' => $blob,
			'shortref' => $blob->getShortHash(),
			'longref' => $blob->getHash(),
			'ref' => $repository->getCurrentBranch(),
		] ) );
	}

	#[Route(
		'/{repo}/history/{commitish}',
		name: 'blob_history',
		requirements: [ 'repo' => '%valid_repository_name%', 'commitish' => '%valid_commitish_format%' ],
	)]
	public function showHistory( Request $request, string $repo, string $commitish ): Response
	{
		$page = (int) $request->query->get( 'page', 1 );
		$perPage = (int) $request->query->get( 'perPage', $this->perPage );

		$repository = $this->index->getRepository( $repo );
		$blob = $repository->getBlob( $commitish );
		$commits = $repository->getCommits( $commitish, $page, $perPage );
		$commitGroups = [];

		foreach( $commits as $commit )
		{
			$commitGroups[$commit->getCommitedAt()->format( 'Y-m-d' )][] = $commit;
		}

		return new Response( $this->templating->render( 'Blob/history.html.twig', [
			'repository' => $repository,
			'blob' => $blob,
			'commitGroups' => $commitGroups,
			'commitish' => $commitish,
			'page' => $page,
			'nextPage' => $page + 1,
			'previousPage' => $page - 1,
			'perPage' => $perPage,
			'shortref' => '',
			'longref' => '',
			'ref' => $repository->getCurrentBranch(),
		] ) );
	}
}
