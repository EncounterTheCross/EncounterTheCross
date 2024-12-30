<?php

namespace App\Twig\Components;

use App\Repository\TestimonialRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent('TestimonialCollectionComponent')]
final class TestimonialCollectionComponent
{
    use GridConfigurationTrait;

    public const LAYOUT_FEATURED = 'Featured';
    public const LAYOUT_HALF_RIGHT = 'HalfRight';
    public const LAYOUT_HALF_LEFT = 'HalfLeft';
    public const LAYOUT_GRID = 'Grid';
    public const LAYOUT_MATH_ROUNDING_MODE_AUTO = 'Auto';
    public const LAYOUT_MATH_ROUNDING_MODE_DOWN = 'Lose';
    public const LAYOUT_MATH_ROUNDING_MODE_UP = 'Strict';
    public const TOTAL_GRID_COLUMNS = 12;
    public const MAX_PER_PAGE = 4;

    #[ExposeInTemplate('isLayoutGrid')]
    private bool $grid = false;
    #[ExposeInTemplate('isLayoutFeatured')]
    private bool $featured = false;
    #[ExposeInTemplate('isLayoutHalfRight')]
    private bool $rightOrientation = true;

    private int $limit = -1;

    /**
     * @var int Default 1. All Testimonies fit in one row unless we are doing GRID Layout.
     */
    #[ExposeInTemplate]
    private int $cardsPerRow = 1;
    #[ExposeInTemplate]
    private bool $pagination;
    #[ExposeInTemplate]
    private ?string $background;

    #[ExposeInTemplate]
    private ?Pagerfanta $pagerfanta = null;
    private Request $request;

    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
    ) {
    }

    public function hasPagination(): bool
    {
        return $this->pagination;
    }

    public function getBackground(): string
    {
        return $this->background ?? '';
    }

    public function getRightOrientation(): bool
    {
        return $this->rightOrientation;
    }

    public function getGrid(): bool
    {
        return $this->grid;
    }

    public function getFeatured(): bool
    {
        return $this->featured;
    }

    public function getCardsPerRow(): int
    {
        return $this->cardsPerRow;
    }

    public function getPagerfanta(): ?Pagerfanta
    {
        return $this->pagerfanta;
    }

    #[PreMount]
    public function preMount(array $data): array
    {
        // validate data
        $resolver = new OptionsResolver();
        $resolver->define('gridMode')
            ->default(self::LAYOUT_MATH_ROUNDING_MODE_AUTO)
            ->allowedValues(
                self::LAYOUT_MATH_ROUNDING_MODE_AUTO,
                self::LAYOUT_MATH_ROUNDING_MODE_UP,
                self::LAYOUT_MATH_ROUNDING_MODE_DOWN,
            )
            ->allowedTypes('string');
        $resolver->define('layout')
            ->allowedValues(
                self::LAYOUT_FEATURED,
                self::LAYOUT_HALF_RIGHT,
                self::LAYOUT_HALF_LEFT,
                self::LAYOUT_GRID,
            )
            ->required()
            ->allowedTypes('string');
        $resolver->define('limit')
            ->allowedTypes('int')
            ->default(-1);
        $resolver->define('background')
            ->default(null)
            ->allowedTypes('string', 'null')
            ->required();

        // Pagination
        $resolver->define('pagination')
            ->required()
            ->allowedTypes('boolean')
            ->default(function (Options $options): bool {
                return -1 === $options['limit'];
            });
        // Current Page
        $resolver->define('request')
            ->required()
            ->allowedTypes(Request::class)
        ;

        return $resolver->resolve($data);
    }

    public function mount(
        Request $request,
        string $layout,
        int $limit,
        string $gridMode,
        bool $pagination,
        ?string $background,
    ): void {
        $this->limit = $limit;
        $this->pagination = $pagination;
        $this->background = $background;
        $this->request = $request;

        match ($layout) {
            self::LAYOUT_FEATURED => $this->setupFeaturedLayout(),
            self::LAYOUT_HALF_RIGHT,self::LAYOUT_HALF_LEFT => $this->setupHalfLayout($layout),
            self::LAYOUT_GRID => $this->setupGridLayout($gridMode),
        };
    }

    #[ExposeInTemplate]
    public function getTestimonies(): Pagerfanta|array
    {
        if ($this->pagination) {
            $queryBuilder = $this->testimonialRepository->findAllTestimoniesQueryBuilder();
            $adaptor = new QueryAdapter($queryBuilder);

            $testimonies = Pagerfanta::createForCurrentPageWithMaxPerPage(
                $adaptor,
                $this->request->query->get('page', 1),
                self::MAX_PER_PAGE
            );

            return $testimonies;
        }
        $testimonies = $this->testimonialRepository->findBy(
            [],
            null,
            $this->limit > 0 ? $this->limit : null
        );

        return $testimonies;
    }

    protected function setupFeaturedLayout(): void
    {
        $this->featured = true;
        $this->setCardColumns([
            'breakpoints' => [
                [
                    'lg' => 7,
                    'xl' => 8,
                ],
                [
                    'lg' => 5,
                    'xl' => 4,
                ],
            ],
        ]);
    }

    protected function setupHalfLayout(string $orientation): void
    {
        self::LAYOUT_HALF_RIGHT === $orientation
            ? $this->rightOrientation = true
            : $this->rightOrientation = false;

        $this->setCardColumns([
            'breakpoints' => [['lg' => 6]],
        ]);
    }

    protected function setupGridLayout(string $gridMode, int $columns = 6): void
    {
        $this->grid = true;
        // Ex: ( ( 12 / 3 ) * 100) / 100 = 4
        $cardColumns = $this->cardSizeAlgorithm($columns);
        // makes sure we always have an even number
        $mathType = match ($gridMode) {
            self::LAYOUT_MATH_ROUNDING_MODE_DOWN => function ($columnAlgorithm) {return floor($columnAlgorithm); },
            self::LAYOUT_MATH_ROUNDING_MODE_UP => function ($columnAlgorithm) {return ceil($columnAlgorithm); },
            default => function ($columnAlgorithm) {return round($columnAlgorithm); },
        };
        $size = $mathType($cardColumns);
        $this->setCardColumns($size);
        $this->cardsPerRow = $mathType($size);
    }

    private function cardSizeAlgorithm(int $columns): float|int
    {
        return ((self::TOTAL_GRID_COLUMNS / $columns) * 100) / 100;
    }
}
