<?php

namespace pallo\library\cms\theme;

use pallo\library\template\theme\ThemeModel as LibraryThemeModel;

/**
 * Filters CMS themes from the template theme model
 */
class TemplateThemeModel implements ThemeModel {

    /**
     * Constructs a new theme model
     * @param pallo\library\template\theme\ThemeModel $model
     * @return null
     */
    public function __construct(LibraryThemeModel $model) {
        $this->model = $model;
    }

    /**
     * Gets a theme
     * @param string $name Machine name of the theme
     * @return Theme
     * @throws pallo\library\template\exception\ThemeNotFoundException
     */
    public function getTheme($name) {
        $theme = $this->model->getTheme($name);

        if (!$theme instanceof Theme) {
            throw new ThemeNotFoundException($name);
        }

        return $theme;
    }

    /**
     * Gets the available themes
     * @return array Array with the machine name of the theme as key and an
     * instance of Theme as value
    */
    public function getThemes() {
        $themes = $this->model->getThemes();

        foreach ($themes as $name => $theme) {
            if (!$theme instanceof Theme) {
                unset($themes[$name]);
            }
        }

        return $themes;
    }

}