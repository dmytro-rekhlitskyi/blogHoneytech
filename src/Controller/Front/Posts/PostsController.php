<?php

namespace App\Controller\Front\Posts;

use App\DTO\Posts\SearchPostsRequest;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\PaginationService;
use App\Service\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class PostsController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly PaginationService $paginationService,
        private readonly PostService $postService,
    ) {
    }

    #[Route('/posts', name: 'front_posts', methods: ['GET'])]
    public function index(#[MapQueryString] SearchPostsRequest $request): Response
    {
        $page = $request->page ?? 1;
        $limit = $request->limit ?? 20;
        $tagSlug = $request->tagSlug;

        if (empty($tagSlug)) {
            $tagSlug = null;
        }

        $paginator = $this->postRepository->findPaginated($tagSlug, $page, $limit);
        $meta = $this->paginationService->createMeta($paginator, $page, $limit);

        $deleteForms = [];
        foreach ($paginator as $post) {
            $deleteForms[$post->getId()] = $this->createFormBuilder()
                ->getForm()
                ->createView();
        }

        return $this->render('posts/index.html.twig', [
            'posts' => $paginator,
            'meta' => $meta,
            'tagSlug' => $tagSlug,
            'delete_forms' => $deleteForms,
        ]);
    }

    #[Route('/posts/create', name: 'front_posts_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->postService->create($post);

                $this->addFlash('success', 'Post successfully created');

                return $this->redirectToRoute('front_posts');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('posts/form.html.twig', [
            'form' => $form,
            'post' => $post,
        ]);
    }

    #[Route('/posts/{id}/edit', name: 'front_posts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->postService->update($post);

                $this->addFlash('success', 'Post successfully updated');

                return $this->redirectToRoute('front_posts');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('posts/form.html.twig', [
            'form' => $form,
            'post' => $post,
        ]);
    }

    #[Route('/posts/{id}/delete', name: 'front_posts_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $form = $this->createFormBuilder()
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->postService->delete($post);

                $this->addFlash('success', 'Post successfully deleted');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Invalid security token');
        }

        return $this->redirectToRoute('front_posts');
    }

    #[Route('/posts/{slug}', name: 'front_posts_show', methods: ['GET'])]
    public function show(string $slug): Response
    {
        $post = $this->postRepository->findOneBy(['slug' => $slug]);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $form = $this->createForm(PostType::class, $post);

        return $this->render('posts/show.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }
}
