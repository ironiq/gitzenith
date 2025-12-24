<?php

declare( strict_types=1 );

namespace GitZenith\App\Controller;

use GitZenith\App\Form\CriteriaType;
use GitZenith\Repository\Index;
use GitZenith\SCM\Commit\Criteria;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
// use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
// use Symfony\Component\HttpFoundation\Session\Session;
// use Symfony\Component\HttpFoundation\Session\FlashBagInterface;
use Twig\Environment;

class CommitSearch
{
	public function __construct( protected Environment $templating, protected Index $index, protected FormFactoryInterface $formFactory, protected RouterInterface $router )
	{
	}

	#[Route(
		'/{repo}/search/commits/{commitish}',
		name: 'repository_search_commits',
		requirements: [ 'repo' => '%valid_repository_name%', 'commitish' => '%valid_commitish_format%' ],
	)]
	public function createForm( string $repo, string $commitish ): Response
	{
		$repository = $this->index->getRepository( $repo );
		$form = $this->formFactory->create( CriteriaType::class, new Criteria() );

		return new Response( $this->templating->render( 'Search/form.html.twig', [
			'repository' => $repository,
			'commitish' => $commitish,
			'form' => $form->createView(),
		] ) );
	}

	#[Route(
		'/{repo}/search/commits/{commitish}',
		name: 'repository_search_commits_action',
		requirements: [ 'repo' => '%valid_repository_name%', 'commitish' => '%valid_commitish_format%' ],
	)]
	public function showResults( Request $request, string $repo, string $commitish ): Response
	{
		$criteria = new Criteria();
		$mes = $request->request->get( 'query', '' );
		$mes = ( $mes === '' ) ? null : strval( $mes );
		$criteria->setMessage( $mes );

		$form = $this->formFactory->create( CriteriaType::class, $criteria );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && !$form->isValid() )
		{
			dd( $form->getErrors( true ) );
			// $session = $request->getSession();
			// foreach( $form->getErrors( true ) as $error )
			// {
			// 	/* @disregard P1013 false positive */
			// 	if ($session instanceof FlashBagAwareSessionInterface)
			// 	{
			// 		$session->getFlashBag()->add( 'danger', $error->getMessage() );
			// 	}
			// }

			return new RedirectResponse( $this->router->generate( 'repository_tree', [
				'repository' => $repository,
				'commitish' => $commitish,
			] ) );
		}

		$repository = $this->index->getRepository( $repo );
		$commits = $repository->searchCommits( $form->getData(), $commitish );
		$commitGroups = [];

		foreach( $commits as $commit )
		{
			$commitGroups[$commit->getCommitedAt()->format( 'Y-m-d' )][] = $commit;
		}

		return new Response( $this->templating->render( 'Search/list.html.twig', [
			'repository' => $repository,
			'commitGroups' => $commitGroups,
			'commitish' => $commitish,
		] ) );
	}
}
