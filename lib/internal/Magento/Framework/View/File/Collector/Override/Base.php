<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File\Collector\Override;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Module\Dir\Search;
use Magento\Framework\Module\ThemeDir;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\AbstractCollector;
use Magento\Framework\View\File\Factory as FileFactory;
use Magento\Framework\View\Helper\PathPattern as PathPatternHelper;

/**
 * Source of view files that explicitly override base files introduced by modules
 */
class Base extends AbstractCollector
{
    /**
     * @var ThemeDir
     */
    protected $themeDir;

    /**
     * @var ReadInterface
     */
    protected $rootDirectory;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param PathPatternHelper $pathPatternHelper
     * @param string $subDir
     * @param ThemeDir $themeDir
     */
    public function __construct(
        Filesystem $filesystem,
        FileFactory $fileFactory,
        PathPatternHelper $pathPatternHelper,
        $subDir = '',
        ThemeDir $themeDir
    ) {
        $this->themeDir = $themeDir;
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        parent::__construct($filesystem, $fileFactory, $pathPatternHelper, $subDir);
    }

    /**
     * Retrieve files
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $namespace = $module = '*';
        $themePath = $theme->getFullPath();
        $searchPattern = "{$namespace}_{$module}/{$this->subDir}{$filePath}";
        $files = $this->rootDirectory->search($searchPattern, $this->themeDir->getPathByKey($themePath));
        $result = [];
        $pattern = "#(?<moduleName>[^/]+)/{$this->subDir}"
            . $this->pathPatternHelper->translatePatternFromGlob($filePath) . "$#i";
        foreach ($files as $file) {
            $filename = $this->directory->getAbsolutePath($file);
            if (!preg_match($pattern, $filename, $matches)) {
                continue;
            }
            $result[] = $this->fileFactory->create($filename, $matches['moduleName']);
        }
        return $result;
    }

    /**
     * Get scope directory of this file collector
     *
     * @return string
     */
    protected function getScopeDirectory()
    {
        return DirectoryList::THEMES;
    }
}
