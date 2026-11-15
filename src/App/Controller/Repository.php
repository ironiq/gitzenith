<?php

declare( strict_types=1 );

namespace GitZenith\App\Controller;

use GitZenith\Repository\Index;
use GitZenith\SCM\Exception\CommandException;
use GitZenith\SCM\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

// use GitZenith\Repository\Commitish;

class Repository
{
	public function __construct( protected Environment $templating, protected Index $index )
	{
	}

	#[Route( '/', name: 'repository_list' )]
	public function list(): Response
	{
		$repositories = $this->index->getRepositories();

		ksort( $repositories );

		return new Response( $this->templating->render( 'Repository/list.html.twig', [
			'repositories' => $repositories,
		] ) );
	}

	#[Route(
		'/{repo}',
		name: 'repository_show',
		requirements: [ 'repo' => '%valid_repository_name%' ],
	)]
	public function show( string $repo ): Response
	{
		$repository = $this->index->getRepository( $repo );
		try
		{
			$tree = $repository->getTree();
		}
		catch ( CommandException $e )
		{
			$tree = false;
		}
		if( $tree )
		{
			// dd($tree->getHash());
			$lastCommit = $repository->getCommit( $tree->getHash() );
			$readme = $tree->getReadme();

			if( $readme )
			{
				$blob = $repository->getBlob( $lastCommit->getHash() . '/' . $readme->getName() );
				$readme = File::createFromBlob( $blob );
			}
			$tpl = 'Repository/show.html.twig';
		}
		else
		{
			$tpl = 'Repository/empty.html.twig';
			$tree = false;
			$lastCommit = false;
			$readme = false;
		}

		return new Response( $this->templating->render( $tpl, [
			'repository' => $repository,
			'tree' => $tree,
			'lastCommit' => $lastCommit,
			'readme' => $readme,
			'shortref' => '',
			'longref' => $repository->getCurrentBranch(),
			'ref' => $repository->getCurrentBranch(),
		] ) );
	}

	#[Route(
		'/{repo}/tree/{commitish<.*>}',
		name: 'repository_tree',
		requirements: [ 'repo' => '%valid_repository_name%' ],
		defaults: [ 'commitish' => '' ],
	)]
	public function showTree( string $repo, ?string $commitish = null ): Response
	{
		$repository = $this->index->getRepository( $repo );

		if( $commitish === null )
		{
			$commitish = '/';
		}
		$tree = $repository->getTree( $commitish );
		$lastCommit = $repository->getLastCommitFromPath( $tree->getName(), $tree->getHash() );
		$readme = $tree->getReadme();
		$shortref = $lastCommit->getShortHash();
		$longref = $lastCommit->getHash();

		if( $readme )
		{
			$blob = $repository->getBlob( $tree->getHash() . '/' . $readme->getName() );
			$readme = File::createFromBlob( $blob );
		}

		return new Response( $this->templating->render( 'Repository/show.html.twig', [
			'repository' => $repository,
			'tree' => $tree,
			'lastCommit' => $lastCommit,
			'readme' => $readme,
			'shortref' => $shortref,
			'longref' => $longref,
			'ref' => $repository->getCurrentBranch(),
		] ) );
	}

	#[Route(
		'/{repo}/branches',
		name: 'repository_list_branches',
		requirements: [ 'repo' => '%valid_repository_name%' ],
	)]
	public function listBranches( string $repo ): Response
	{
		$repository = $this->index->getRepository( $repo );
		$branches = $repository->getBranches();

		return new Response( $this->templating->render( 'Repository/branches.html.twig', [
			'repository' => $repository,
			'branches' => $branches,
		] ) );
	}

	#[Route(
		'/{repo}/tags',
		name: 'repository_list_tags',
		requirements: [ 'repo' => '%valid_repository_name%' ],
	)]
	public function listTags( string $repo ): Response
	{
		$repository = $this->index->getRepository( $repo );
		$tags = $repository->getTags();

		return new Response( $this->templating->render( 'Repository/tags.html.twig', [
			'repository' => $repository,
			'tags' => $tags,
		] ) );
	}

	#[Route(
		'/{repo}/archive/{commitish}.{format}',
		name: 'repository_archive',
		requirements: [ 'repo' => '%valid_repository_name%', 'commitish' => '%valid_commitish_format%', 'format' => '(zip|tar|tar.gz)' ],
	)]
	public function archive( string $repo, string $commitish, string $format ): Response
	{
		$repository = $this->index->getRepository( $repo );
		$archive = $repository->archive( $format, $commitish );

		if( !file_exists( $archive ) )
		{
			throw new NotFoundHttpException();
		}

		$archivecontent = file_get_contents( $archive );
		if( !$archivecontent )
		{
			$archivecontent = null;
		}
		$response = new Response( $archivecontent );
		$disposition = $response->headers->makeDisposition( ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename( $archive ) );
		$response->headers->set( 'Content-Disposition', $disposition );
		$response->headers->set( 'Content-Type', 'application/octet-stream' );

		return $response;
	}
}
