<?php

namespace App\Controller\Front\Tags;

use App\DTO\Tags\SearchTagsRequest;
use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use App\Service\PaginationService;
use App\Service\TagService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class TagsController extends AbstractController
{
    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly PaginationService $paginationService,
        private readonly TagService $tagService,
    ) {
    }

    #[Route('/tags', name: 'front_tags', methods: ['GET'])]
    public function index(#[MapQueryString] SearchTagsRequest $request): Response
    {
        $page = $request->page ?? 1;
        $limit = $request->limit ?? 20;
        $query = $request->query ?? null;

        $paginator = $this->tagRepository->findPaginated($query, $page, $limit);
        $meta = $this->paginationService->createMeta($paginator, $page, $limit);

        $deleteForms = [];
        foreach ($paginator as $tag) {
            $deleteForms[$tag->getId()] = $this->createFormBuilder()
                ->getForm()
                ->createView();
        }

        return $this->render('tags/index.html.twig', [
            'tags' => $paginator,
            'meta' => $meta,
            'query' => $query,
            'delete_forms' => $deleteForms,
        ]);
    }

    #[Route('/tags/create', name: 'front_tags_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->tagService->create($tag);

                $this->addFlash('success', 'Tag successfully created');

                return $this->redirectToRoute('front_tags');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('tags/form.html.twig', [
            'form' => $form,
            'tag' => $tag,
        ]);
    }

    #[Route('/tags/{id}/edit', name: 'front_tags_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            throw $this->createNotFoundException('Tag not found');
        }

        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->tagService->update($tag);

                $this->addFlash('success', 'Tag successfully updated');

                return $this->redirectToRoute('front_tags');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('tags/form.html.twig', [
            'form' => $form,
            'tag' => $tag,
        ]);
    }

    #[Route('/tags/{id}/delete', name: 'front_tags_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            throw $this->createNotFoundException('Tag not found');
        }

        $form = $this->createFormBuilder()
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->tagService->delete($tag);

                $this->addFlash('success', 'Tag successfully deleted');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Invalid security token');
        }

        return $this->redirectToRoute('front_tags');
    }
}
