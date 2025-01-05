<?php

declare( strict_types=1 );

namespace GitZenith\App\Controller;

use GitZenith\Repository\Index;
use GitZenith\SCM\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Twig\Environment;

class Blob
{
	public function __construct( protected Environment $templating, protected Index $index, protected int $perPage )
	{
	}

	public function show( string $repository, string $commitish ): Response
	{
		$arr = explode( '/', $commitish );
		$commitid = $arr[0];

		$repository = $this->index->getRepository( $repository );
		$curbranch = $repository->getCurrentBranch();

		$branches = [];
		foreach( $repository->getBranches( $repository ) as $b )
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
		] ) );
	}

	public function showRaw( string $repository, string $commitish ): Response
	{
		$repository = $this->index->getRepository( $repository );
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

	public function blame( string $repository, string $commitish ): Response
	{
		$repository = $this->index->getRepository( $repository );
		$blob = $repository->getBlob( $commitish );
		$blame = $repository->getBlame( $commitish );

		return new Response( $this->templating->render( 'Blob/blame.html.twig', [
			'repository' => $repository,
			'blame' => $blame,
			'blob' => $blob,
		] ) );
	}

	public function showHistory( Request $request, string $repository, string $commitish ): Response
	{
		$page = (int) $request->query->get( 'page', 1 );
		$perPage = (int) $request->query->get( 'perPage', $this->perPage );

		$repository = $this->index->getRepository( $repository );
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
		] ) );
	}
}
