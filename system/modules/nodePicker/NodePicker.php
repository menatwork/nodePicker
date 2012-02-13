<?php

if (!defined('TL_ROOT'))
    die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2011
 * @package    nodePicker
 * @license    GNU/LGPL
 * @filesource
 */
class NodePicker extends Backend
{

    protected $strSelect;
    protected $intNode;

    protected function findRootPage($intPID)
    {
        $arrPages = $this->Database
                ->prepare("SELECT * FROM tl_page WHERE id=?")
                ->limit(1)
                ->execute($intPID)
                ->fetchAllAssoc();

        if ($arrPages[0]["pid"] == 0)
        {
            return $arrPages[0];
        }
        else
        {
            return $this->findRootPage($arrPages[0]["pid"]);
        }
    }

    public function outputBackendTemplate($strContent, $strTemplate)
    {
        // Check if node is set
        if ($_SESSION['BE_DATA']['tl_page_node'] == 0)
        {
            return $strContent;
        }
        else
        {
            $this->intNode = $_SESSION['BE_DATA']['tl_page_node'];
        }

        // Only for some special sites
        if ($strTemplate == 'be_main' && ($this->Input->get("do") == "article" || $this->Input->get("do") == "page" ) && $this->Input->get("table") == "" && $this->Input->get("act") == "")
        {
            // $GLOBALS['TL_CSS'][] = 'system/modules/nodePicker/html/nodePicker.css';

            $this->strSelect = "";

            $arrCurrentPage = $this->Database->prepare("SELECT id, pid, title FROM tl_page WHERE id = ? ")
                    ->execute($this->intNode)
                    ->fetchAllAssoc();

            if (count($arrCurrentPage) == 0)
            {
                return $strContent;
            }

            $strVersion = (version_compare(VERSION, "2.11", ">=")) ? 'class="version-211" ' : '';
            
            $this->strSelect = '';
            $this->strSelect .= '<div ' . $strVersion . 'id="nodePicker_container"><select class="tl_select" id="nodePicker" name="nodePicker" onchange="window.location=this.options[this.selectedIndex].value">';

            if ($arrCurrentPage[0]['pid'] == 0)
            {
                $arrRootPage = $arrCurrentPage[0];
            }
            else
            {
                $arrRootPage = $this->findRootPage($arrCurrentPage[0]['pid']);
            }

            $this->strSelect .= '<option value="' . $this->Environment->base . $this->Environment->request . "&node=" . $arrRootPage["id"] . '"';
            if ($this->intNode == $arrRootPage["id"])
            {
                $this->strSelect .= ' selected="selected"';
            }
            $this->strSelect .= '>' . $arrRootPage["title"] . '</option>';

            $this->recursivePagination($arrRootPage["id"], 1);

            $this->strSelect .= '</select></div>';

            $pattern = '/<ul.* id=\".*tl_breadcrumb.*\".*>/i';
            preg_match($pattern, $strContent, $matches, PREG_OFFSET_CAPTURE);

            return substr($strContent, 0, $matches[0][1]) . $this->strSelect . substr($strContent, $matches[0][1], strlen($strContent) - $matches[0][1]);
        }

        return $strContent;
    }

    public function recursivePagination($id, $level)
    {
        $strBlank = "";
        for ($i = 0; $i < $level; $i++)
        {
            $strBlank .= "&nbsp;&nbsp;&nbsp;&nbsp;";
        }

        $arrPages = $this->Database->prepare("SELECT id, title FROM tl_page WHERE pid=? ORDER BY sorting")
                ->execute($id)
                ->fetchAllAssoc();

        if (count($arrPages) == 0)
        {
            return;
        }

        foreach ($arrPages as $key => $value)
        {
            $this->strSelect .= '<option value="' . $this->Environment->base . $this->Environment->request . "&node=" . $value["id"] . '"';
            if ($this->intNode == $value["id"])
            {
                $this->strSelect .= ' selected="selected"';
            }
            $this->strSelect .= '>' . $strBlank . $value["title"] . '</option>';

            $this->recursivePagination($value["id"], $level + 1);
        }
    }

}

?>
