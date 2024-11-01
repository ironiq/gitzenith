<?php

declare(strict_types=1);

namespace GitZenith\App\Twig;

// use League\CommonMark\Environment\Environment;
// use League\CommonMark\Extension\Autolink\AutolinkExtension;
// use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
// use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
// use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\CommonMarkConverter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    private CommonMarkConverter $converter;

    public function __construct()
    {
        $environment = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ];

        $this->converter = new CommonMarkConverter($environment);
    }

    public function getFilters()
    {
        return [
            new TwigFilter('markdown', [$this, 'markdown']),
        ];
    }

    public function markdown($string): string
    {
        if (!$string) {
            return '';
        }

        return (string) $this->converter->convert($string);
    }
}
