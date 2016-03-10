<?php

require_once('code/generators/tools/image.php');
require_once('code/generators/tools/filecaching.php');
require_once('config/config.php');

class ArtCache
{
	private static $IMAGE_CACHE_DIMENSIONS = array( array(300, 300),
													array(150, 150),
													array(100, 100),
													array(85, 85),
													array(75, 75),
													array(67, 67),
													array(60, 60),
													array(50, 50));

	public static function GetArtist50x50($name, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedArtist($name, $originalArtworkUrl, 50, 50, $crop);
	}

	public static function GetArtist60x60($name, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedArtist($name, $originalArtworkUrl, 60, 60, $crop);
	}

	public static function GetArtist67x67($name, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedArtist($name, $originalArtworkUrl, 67, 67, $crop);
	}

	public static function GetArtist75x75($name, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedArtist($name, $originalArtworkUrl, 75, 75, $crop);
	}

	public static function GetArtist100x100($name, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedArtist($name, $originalArtworkUrl, 100, 100, $crop);
	}

	public static function GetArtist150x150($name, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedArtist($name, $originalArtworkUrl, 150, 150, $crop);
	}

	public static function GetArtist300x300($name, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedArtist($name, $originalArtworkUrl, 300, 300, $crop);
	}

	public static function GetAlbum50x50($artist, $title, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedAlbum($artist, $title, $originalArtworkUrl, 50, 50, $crop);
	}

	public static function GetAlbum60x60($artist, $title, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedAlbum($artist, $title, $originalArtworkUrl, 60, 60, $crop);
	}

	public static function GetAlbum67x67($artist, $title, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedAlbum($artist, $title, $originalArtworkUrl, 67, 67, $crop);
	}

	public static function GetAlbum75x75($artist, $title, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedAlbum($artist, $title, $originalArtworkUrl, 75, 75, $crop);
	}

	public static function GetAlbum85x85($artist, $title, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedAlbum($artist, $title, $originalArtworkUrl, 85, 85, $crop);
	}

	public static function GetAlbum100x100($artist, $title, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedAlbum($artist, $title, $originalArtworkUrl, 100, 100, $crop);
	}

	public static function GetAlbum150x150($artist, $title, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedAlbum($artist, $title, $originalArtworkUrl, 150, 150, $crop);
	}

	public static function GetAlbum300x300($artist, $title, $originalArtworkUrl, $crop)
	{
		return ArtCache::GetResizedAlbum($artist, $title, $originalArtworkUrl, 300, 300, $crop);
	}

	public static function DeleteArtistCache($artist)
	{
		$hash = ArtCache::GetHash($artist);

		ArtCache::DeleteCacheInPath('filecache_system_artistart_original_path', $hash);
		ArtCache::DeleteCacheInPath('filecache_system_artistart_100x100_path', $hash);
		ArtCache::DeleteCacheInPath('filecache_system_artistart_75x75_path', $hash);
		ArtCache::DeleteCacheInPath('filecache_system_artistart_67x67_path', $hash);
		ArtCache::DeleteCacheInPath('filecache_system_artistart_60x60_path', $hash);
		ArtCache::DeleteCacheInPath('filecache_system_artistart_50x50_path', $hash);
	}

	public static function DeleteAlbumCache($artist, $album)
	{
		$filename = ArtCache::GetHash($artist) . '_' . ArtCache::GetHash($album) . '.png';

		ArtCache::RemoveAllImagesForFilename(IMAGE_CACHE_ALBUMART_PATH, $filename);
	}

	private static function GetResizedAlbum($artist, $title, $originalArtworkUrl, $width, $height, $crop)
	{
		$filename = ArtCache::GetHash($artist) . '_' . ArtCache::GetHash($title) . '.png';

		return ArtCache::GetResizedFromHash($originalArtworkUrl, IMAGE_CACHE_ALBUMART_PATH,
												$filename, $width, $height, $crop, IMAGE_CACHE_ALBUM_ART_MAX_AGE);
	}

	private static function GetResizedArtist($name, $originalArtworkUrl, $width, $height, $crop)
	{
		$filename = ArtCache::GetHash($name) . '.png';

		return ArtCache::GetResizedFromHash($originalArtworkUrl, IMAGE_CACHE_ARTISTART_PATH,
												$filename, $width, $height, $crop, IMAGE_CACHE_ARTIST_ART_MAX_AGE);
	}


	private static function RemoveAllImagesForFilename($basePath, $filename)
	{
		foreach (ArtCache::$IMAGE_CACHE_DIMENSIONS as $imageDimensions)
		{
			$width = $imageDimensions[0];
			$height = $imageDimensions[1];

			$path = ArtCache::GetImagePath($basePath, $filename, $width, $height);

			if (file_exists($path))
			{
				unlink($path);
			}
		}
	}

	private static function GetResizedFromHash($originalArtworkUrl, $basePath, $filename, $width, $height, $crop, $cacheTime)
	{
		$fullPathOriginal = ArtCache::GetImagePath($basePath, $filename, 300, 300);
		$fullPathResized = ArtCache::GetImagePath($basePath, $filename, $width, $height);

		// 1. check if original file is valid
		if (FileCaching::IsFileValid($fullPathOriginal, $cacheTime))
		{
			// 1.1 if so, we return the resized one
			return Image::Open($fullPathResized);
		}
		else
		{
			// 1.2 out of date, or does not exist, to be sure we clean them all up
			ArtCache::RemoveAllImagesForFilename($basePath, $filename);
		}

		// 2. download image
		$imgOriginal = Image::ReadImage($originalArtworkUrl);

		// 2.1 leave if no image was downloaded
		if (!$imgOriginal)
		{
			return false;
		}

		// 3. create smaller versions
		if ($crop)
		{
			$img300x300 = ArtCache::CreateResizedAndCroppedVersion($imgOriginal, $basePath, $filename, 300, 300);
			$img150x150 = ArtCache::CreateResizedVersion($img300x300, $basePath, $filename, 150, 150);
			$img100x100 = ArtCache::CreateResizedVersion($img150x150, $basePath, $filename, 100, 100);
			$img85x85 = ArtCache::CreateResizedVersion($img150x150, $basePath, $filename, 85, 85);
			$img75x75 = ArtCache::CreateResizedVersion($img150x150, $basePath, $filename, 75, 75);
			$img67x67 = ArtCache::CreateResizedVersion($img100x100, $basePath, $filename, 67, 67);
			$img60x60 = ArtCache::CreateResizedVersion($img100x100, $basePath, $filename, 60, 60);
			$img50x50 = ArtCache::CreateResizedVersion($img100x100, $basePath, $filename, 50, 50);
		}
		else
		{
			$img300x300 = ArtCache::CreateResizedVersion($imgOriginal, $basePath, $filename, 300, 300);
			$img150x150 = ArtCache::CreateResizedVersion($imgOriginal, $basePath, $filename, 150, 150);
			$img100x100 = ArtCache::CreateResizedVersion($img150x150, $basePath, $filename, 100, 100);
			$img85x85 = ArtCache::CreateResizedVersion($img150x150, $basePath, $filename, 85, 85);
			$img75x75 = ArtCache::CreateResizedVersion($img150x150, $basePath, $filename, 75, 75);
			$img67x67 = ArtCache::CreateResizedVersion($img100x100, $basePath, $filename, 67, 67);
			$img60x60 = ArtCache::CreateResizedVersion($img100x100, $basePath, $filename, 60, 60);
			$img50x50 = ArtCache::CreateResizedVersion($img100x100, $basePath, $filename, 50, 50);
		}

		// 3.1 remove them all from memory
		@imagedestroy($imgOriginal);
		@imagedestroy($img300x300);
		@imagedestroy($img150x150);
		@imagedestroy($img100x100);
		@imagedestroy($img85x85);
		@imagedestroy($img75x75);
		@imagedestroy($img67x67);
		@imagedestroy($img60x60);
		@imagedestroy($img50x50);

		// 4. return correct image
		return Image::Open($fullPathResized);
	}

	private static function GetHash($name)
	{
		return hash('ripemd160', strtolower($name));
	}

	private static function DeleteCacheInPath($key, $hash)
	{
		$fullPath = $GLOBALS['absolute_path'] . $GLOBALS[$key];

		$mask = $fullPath . '/' . $hash . '*';

		@array_map("unlink", glob($mask));
	}

	private static function GetImagePath($basePath, $targetName, $width, $height)
	{
		$subPath = substr($targetName, 0, 2);

		$path = $basePath . $width . 'x' . $height . "/$subPath/";

		@mkdir($path);

		return $path . $targetName;
	}

	private static function CreateResizedVersion($imgOriginal, $basePath, $targetName, $width, $height)
	{
		$outputPath = ArtCache::GetImagePath($basePath, $targetName, $width, $height);

		$originalWidth  = imagesx($imgOriginal);
		$originalHeight = imagesy($imgOriginal);

		if ($width == $originalWidth && $height == $originalHeight)
		{
			imagepng($imgOriginal, $outputPath);

			return $imgOriginal;
		}
		else
		{
			$img = imagecreatetruecolor($width, $height);

			imagecopyresampled($img, $imgOriginal, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

			imagepng($img, $outputPath);

			return $img;
		}
	}

	private static function CreateResizedAndCroppedVersion($imgOriginal, $basePath, $targetName, $width, $height)
	{
		$outputPath = ArtCache::GetImagePath($basePath, $targetName, $width, $height);

		$originalWidth  = imagesx($imgOriginal);
		$originalHeight = imagesy($imgOriginal);

		if ($width == $originalWidth && $height == $originalHeight)
		{
			imagepng($imgOriginal, $outputPath);

			return $imgOriginal;
		}
		else
		{
			$img = imagecreatetruecolor($width, $height);

			$ratio = $originalWidth / $originalHeight;

			$topX = 0;
			$topY = 0;

			$sampleWidth  = $originalWidth;
			$sampleHeight = $originalHeight;

			if ($ratio >= 1.0)
			{
				$topX = ($originalWidth - $originalHeight) / 2;
				$sampleWidth = $sampleHeight;
			}
			else
			{
				$topY = ($originalHeight - $originalWidth) / 2;
				$sampleHeight = $sampleWidth;
			}

			imagecopyresampled($img, $imgOriginal, 0, 0, $topX, $topY, $width, $height, $sampleWidth, $sampleHeight);

			imagepng($img, $outputPath);

			return $img;
		}
	}
}
