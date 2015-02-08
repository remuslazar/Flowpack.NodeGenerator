<?php
namespace Flowpack\NodeGenerator\Generator;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.NodeGenerator".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Resource\ResourceManager;
use TYPO3\Media\Domain\Model\Image;
use TYPO3\Media\Domain\Model\ImageInterface;
use TYPO3\Media\Domain\Model\ImageVariant;
use TYPO3\Media\Domain\Repository\ImageRepository;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeType;

/**
 * Node Generator
 */
abstract class AstractNodeGeneratorImplementation implements NodeGeneratorImplementationInterface {

	/**
	 * @Flow\Inject
	 * @var ResourceManager
	 */
	protected $resourceManager;

	/**
	 * @Flow\Inject
	 * @var ImageRepository
	 */
	protected $imageRepository;

	/**
	 * @param array $imageThumbnailOptions
	 * @return ImageVariant
	 */
	protected function getRandommImageVariant($imageThumbnailOptions = NULL) {
		$randomFilename = sprintf('Sample%d.jpg', rand(1,3));
		$query = $this->imageRepository->createQuery();
		$result = $query->matching($query->like('resource.filename', $randomFilename))->execute();
		if (!($result && $image = $result->getFirst())) {
			$image = new Image($this->resourceManager->importResource(sprintf('resource://Flowpack.NodeGenerator/Private/Images/%s',$randomFilename)));
			$this->imageRepository->add($image);
		}
		return $image->createImageVariant(array(
			array(
				// set the cropping accordingly, else the Neos Backend
				// Image Inspector is somehow broken..
				'command' => 'crop',
				'options' => array(
					'start' => array(
						'x' => 0,
						'y' => 0,
					),
					'size' => array(
						'width' => $image->getWidth(),
						'height' => $image->getHeight(),
					),
				),
			),
			array(
				'command' => 'thumbnail',
				'options' => $imageThumbnailOptions ? $imageThumbnailOptions : array(
					'size' => array(
						'width' => 1920, // full-hd resolution
						'height' => 1280
					)
				),
			),
		));
	}

	/**
	 * @param NodeInterface $parentNode
	 * @param NodeType $nodeType
	 * @return NodeInterface The freshly created node
	 */
	abstract public function create(NodeInterface $parentNode, NodeType $nodeType);
}