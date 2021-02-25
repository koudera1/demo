<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;


use App\Repository\PostRepository;

use Twig\Extra\Markdown\DefaultMarkdown;
use Twig\Extra\Markdown\MarkdownRuntime;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

use Knp\Snappy\Pdf;
use App\Service\Twig;

use Twig\Extra\Intl\IntlExtension;
use Twig\Extra\Markdown\MarkdownExtension;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

/**
 * A console command that lists all the existing users.
 *
 * To use this command, open a terminal window, enter into your project directory
 * and execute the following:
 *
 *     $ php bin/console app:list-users
 *
 * Check out the code of the src/Command/AddUserCommand.php file for
 * the full explanation about Symfony commands.
 *
 * See https://symfony.com/doc/current/console.html
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class MakePdfCommand extends Command
{
    // a good practice is to use the 'app:' prefix to group all your custom application commands
    protected static $defaultName = 'app:make-pdf';

    private $posts;
    private $twig;

    public function __construct(PostRepository $posts, Twig $twig)
    {
        $this->twig = $twig;
        parent::__construct();
        $this->posts = $posts;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Generates pdf out of post of the given id')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of a post.')
            ->addArgument('fileName', InputArgument::REQUIRED, 'The name of a file.');
        ;
    }

    /**
     * This method is executed after initialize(). It usually contains the logic
     * to execute to complete this command task.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $post = $this->posts->findOneById($input->getArgument('id'));

        if (null === $post) {
            throw new RuntimeException(sprintf('User with username "%s" not found.', $post));
        }

        $this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new MarkdownExtension());
        $this->twig->addRuntimeLoader(new class implements RuntimeLoaderInterface {
            public function load($class) {
                if (MarkdownRuntime::class === $class) {
                    return new MarkdownRuntime(new DefaultMarkdown());
                }
            }
        });

        $pdf = new Pdf();
        $pdf->setBinary("\"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe\"");
        $pdf->generateFromHtml(
            $this->twig->render('templates/blog/post_pdf.html.twig', ['post' => $post]),
            'C:/Users/koude/' . $input->getArgument('fileName'));

        return Command::SUCCESS;
    }
}
