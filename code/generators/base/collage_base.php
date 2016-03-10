<?php

require_once('code/generators/base/imagegenerator.php');
require_once('code/generators/tools/artcache.php');

abstract class CollageBase extends ImageGenerator
{
    private $type;

    private $usableImages = array();
    private $usableItems = array();

    private $nrOfRows = 0;
    private $nrOfCols = 0;
    private $artSize = 0;

    private $receivedShowPC;

    public function __construct($type, $serverSideRequest = FALSE)
    {
        parent::__construct($serverSideRequest);

        $this->type = $type;
    }

    protected function LoadParameters()
    {
        if (isset($_GET['limit']))
        {
            $this->parameters['cols'] = 3;
            $this->parameters['rows'] = ceil($_GET['limit'] / 3);
        }
        else
        {
            $this->parameters['cols'] = isset($_GET['cols']) ? strtolower($_GET['cols']) : '3';
            $this->parameters['rows'] = isset($_GET['rows']) ? strtolower($_GET['rows']) : '3';
        }

        $this->parameters['border'] = isset($_GET['border']) ? strtolower($_GET['border']) : '';

        $this->parameters['showPC'] = isset($_GET['showPC']) ? strtolower($_GET['showPC']) : 'no';

        $this->receivedShowPC = isset($_GET['showPC']);

        return TRUE;
    }

    protected function ProcessData()
    {
        $usableCount = 0;

        $this->nrOfCols = $this->parameters['cols'];
        $this->nrOfRows = $this->parameters['rows'];

        $maxNrRows = 0;

        switch ($this->parameters['cols'])
        {
            case '1':
                $maxNrRows = 50;
                break;
            case '2':
                $maxNrRows = 25;
                break;
            case '3':
                $maxNrRows = 16;
                break;
            case '4':
                $maxNrRows = 12;
                break;
            case '5':
                $maxNrRows = 10;
                break;
            case '6':
                $maxNrRows = 8;
                break;
        }

        if (!$this->isPriorityUser)
        {
            $maxNrRows = 3;
        }

        $this->nrOfRows = $this->nrOfRows <= $maxNrRows ? $this->nrOfRows : $maxNrRows;

        $this->artSize = 300 / $this->nrOfCols;
        $artLimit = $this->nrOfCols * $this->nrOfRows;

        $artLimit = $artLimit <= 50 ? $artLimit : 50;

        foreach ($this->data as $item)
        {
            if ($this->type == 'album')
            {
                $url = $item['i4'];
                $name = $item['name'];
                $artist = $item['artist'];

                // prefilter bad apples
                if (strpos($url, 'default_album_') != false)
                {
                    continue;
                }

                if ((strpos($url, 'last.fm') == false) && (strpos($url, 'lastfm') == false) && (strpos($url, 'amazon') == false))
                {
                    continue;
                }

                if ($this->debugMode)
                {
                    echo "$artist - $name ($url)<br />";
                }

                switch($this->parameters['cols'])
                {
                    case '1':
                        $artImage = ArtCache::GetAlbum300x300($artist, $name, $url, false);
                        break;
                    case '2':
                        $artImage = ArtCache::GetAlbum150x150($artist, $name, $url, false);
                        break;
                    case '3':
                        $artImage = ArtCache::GetAlbum100x100($artist, $name, $url, false);
                        break;
                    case '4':
                        $artImage = ArtCache::GetAlbum75x75($artist, $name, $url, false);
                        break;
                    case '5':
                        $artImage = ArtCache::GetAlbum60x60($artist, $name, $url, false);
                        break;
                    case '6':
                        $artImage = ArtCache::GetAlbum50x50($artist, $name, $url, false);
                        break;
                }
            }
            else if ($this->type == 'artist')
            {
                $url = $item['i4'];
                $name = $item['name'];

                // prefilter bad apples
                if ($url == '')
                {
                    continue;
                }

                switch($this->parameters['cols'])
                {
                    case '1':
                        $artImage = ArtCache::GetArtist300x300($name, $url, true);
                        break;
                    case '2':
                        $artImage = ArtCache::GetArtist150x150($name, $url, true);
                        break;
                    case '3':
                        $artImage = ArtCache::GetArtist100x100($name, $url, true);
                        break;
                    case '4':
                        $artImage = ArtCache::GetArtist75x75($name, $url, true);
                        break;
                    case '5':
                        $artImage = ArtCache::GetArtist60x60($name, $url, true);
                        break;
                    case '6':
                        $artImage = ArtCache::GetArtist50x50($name, $url, true);
                        break;
                }
            }

            // if valid image, then add it to list and check if we reached the set cover limit
            if ($artImage)
            {
                $this->usableImages[] = $artImage;
                $this->usableItems[] = $item;

                if (++$usableCount >= $artLimit)
                {
                    break;
                }
            }
        }

        $this->nrOfRows = floor(count($this->usableImages) / $this->nrOfCols);

        if ($this->nrOfRows == 0)
        {
            return FALSE;
        }

        return TRUE;
    }

    protected function GetClientImageHeight()
    {
        return $this->nrOfRows * $this->artSize;
    }

    protected function GetImageType()
    {
        return '.jpg';
    }

    protected function GetFileCacheParameters()
    {
        if ($this->receivedShowPC)
        {
            return array('rows', 'cols', 'border', 'showPC');
        }
        else
        {
            return array('rows', 'cols', 'border');
        }
    }

    protected function GetImageQuality()
    {
        if ($this->isPriorityUser)
        {
            return 95;
        }
        else
        {
            return 60;
        }
    }

    public function RenderImage()
    {
        $usableCount = 0;

        if ($this->parameters['showPC'] == 'yes')
        {
            $playCountBackgroundColor = imagecolorallocatealpha($this->imageHandle, 0, 0, 0, 50);
            $playCountTextColor = imagecolorallocate($this->imageHandle, 255, 255, 255);
            $playCountFont = $this->GetFontPath('lucida');
        }

        for ($row = 0; $row < $this->nrOfRows; $row++)
        {
            for ($col = 0; $col < $this->nrOfCols; $col++)
            {
                $artImage = $this->usableImages[$usableCount];
                $item = $this->usableItems[$usableCount];

                $usableCount++;

                $artX = $col * $this->artSize;
                $artY = ($row * $this->artSize) + $this->headerHeight;

                imagecopy($this->imageHandle, $artImage, $artX, $artY, 0, 0, $this->artSize, $this->artSize);

                if ($this->parameters['showPC'] == 'yes')
                {
                    $textSize = 9;

                    $text = $item['PC'];

                    $x1 = $artX;
                    $y1 = ($row + 1) * $this->artSize - 16 + $this->headerHeight;

                    $x2 = $x1 + $this->artSize;
                    $y2 = $y1 + 16;

                    $dimensions = imagettfbbox($textSize, 0, $playCountFont, $text);

                    $width = abs($dimensions[4] - $dimensions[0]);

                    $textX = $x1 + ($this->artSize * 0.5) - ($width * 0.5);
                    $textY = ($row + 1) * $this->artSize - 4 + $this->headerHeight;

                    imagefilledrectangle($this->imageHandle, $x1, $y1, $x2, $y2, $playCountBackgroundColor);
                    imagettftext($this->imageHandle, $textSize, 0, $textX, $textY, $playCountTextColor, $playCountFont, $text);
                }
            }
        }

        // destroy all images
        for ($i = 0; $i < count($this->usableImages); $i++)
        {
            imagedestroy($this->usableImages[$i]);
        }

        $this->RenderBorder($this->nrOfRows, $this->nrOfCols, $this->artSize);

        return TRUE;
    }

    private function RenderBorder($imageNrRows, $imageNrCols, $imageArtSize)
    {
        // add borders if necessary
        if ($this->parameters['border'] != '' && $this->parameters['border'] != 'none')
        {
            $borderColor = $this->GetColorFromName($this->parameters['border']);

            for ($i = 1; $i < $imageNrCols; $i++)
            {
                imageline($this->imageHandle, $i * $imageArtSize, $this->headerHeight, $i * $imageArtSize, $imageNrRows * $imageArtSize + $this->headerHeight, $borderColor);
            }

            for ($i = 1; $i < $imageNrRows; $i++)
            {
                imageline($this->imageHandle, 0, $i * $imageArtSize + $this->headerHeight, 300, $i * $imageArtSize + $this->headerHeight, $borderColor);
            }
        }
    }
}
