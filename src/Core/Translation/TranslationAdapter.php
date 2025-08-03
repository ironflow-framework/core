<?php

namespace IronFlow\Core\Translation;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationAdapter implements TranslatorInterface
{
    protected Translator $translator;

    public function __construct(string $locale = 'fr', string $translationsPath = 'translations')
    {
        $this->translator = new Translator($locale);
        $this->translator->addLoader('yaml', new YamlFileLoader());
        $this->translator->addLoader('yml', new YamlFileLoader());
        $this->translator->addLoader('php', new PhpFileLoader());
        $this->translator->addLoader('json', new JsonFileLoader());

        foreach (glob($translationsPath . '/*') as $localeDir) {
            if (!is_dir($localeDir)) continue;
            $locale = basename($localeDir);
            foreach (glob($localeDir . '/*.{php,yml,yaml,json}', GLOB_BRACE) as $file) {
                $domain = pathinfo($file, PATHINFO_FILENAME);
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $this->translator->addResource($ext, $file, $locale, $domain);
            }
        }
    }

    public function trans($id, array $parameters = [], $domain = null, $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null): string
    {
        $parameters['%count%'] = $number;
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function setLocale(string $locale): void
    {
        $this->translator->setLocale($locale);
    }

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }
}
