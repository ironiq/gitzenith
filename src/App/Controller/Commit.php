<?php

declare( strict_types=1 );

namespace GitZenith\App\Controller;

use GitZenith\Repository\Commitish;
use GitZenith\Repository\Index;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class Commit
{
	public function __construct( protected Environment $templating, protected Index $index, protected int $perPage )
	{
	}

	#[Route(
		'/{repo}/commits/{commitish}',
		name: 'commit_list',
		requirements: [ 'repo' => '%valid_repository_name%', 'commitish' => '%valid_commitish_format%' ],
	)]
	public function list( Request $request, string $repo, string $commitish ): Response
	{
		$page = (int) $request->query->get( 'page', 1 );
		$perPage = (int) $request->query->get( 'perPage', $this->perPage );

		$repository = $this->index->getRepository( $repo );
		$commits = $repository->getCommits( $commitish, $page, $perPage );
		$commitGroups = [];

		foreach( $commits as $commit )
		{
			$commitGroups[$commit->getCommitedAt()->format( 'Y-m-d' )][] = $commit;
		}

		return new Response( $this->templating->render( 'Commit/list.html.twig', [
			'repository' => $repository,
			'commitish' => $commitish,
			'commitGroups' => $commitGroups,
			'page' => $page,
			'nextPage' => $page + 1,
			'previousPage' => $page - 1,
			'perPage' => $perPage,
		] ) );
	}

	#[Route(
		'/{repo}/commit/{commitish}',
		name: 'commit_show',
		requirements: [ 'repo' => '%valid_repository_name%', 'commitish' => '%valid_commitish_format%' ],
	)]
	public function show( string $repo, string $commitish ): Response
	{
		$repository = $this->index->getRepository( $repo );
		$commit = $repository->getCommit( $commitish );

		return new Response( $this->templating->render( 'Commit/show.html.twig', [
			'repository' => $repository,
			'commit' => $commit,
		] ) );
	}

	#[Route(
		'/{repo}/feed/{commitish}.{format}',
		name: 'repository_feed',
		requirements: [ 'repo' => '%valid_repository_name%', 'commitish' => '%valid_commitish_format%', 'format' => '(atom|rss)' ],
	)]
	public function feed( string $repo, string $commitish, string $format ): Response
	{
		$repository = $this->index->getRepository( $repo );
		$commits = $repository->getCommits( $commitish, 1, $this->perPage );
		$commitish = new Commitish( $repository, $commitish );

		return new Response( $this->templating->render( sprintf( 'Commit/feed.%s.twig', $format ), [
			'repository' => $repository,
			'commitish' => $commitish,
			'commits' => $commits,
		] ) );
	}
}
