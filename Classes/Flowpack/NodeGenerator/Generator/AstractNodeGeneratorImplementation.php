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
	 * Inject PersistenceManagerInterface
	 *
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var ImageRepository
	 */
	protected $imageRepository;

	/**
	 * @param array $imageThumbnailOptions
	 * @return ImageInterface
	 */
	protected function getRandomImage($imageThumbnailOptions = NULL) {
		$randomFilename = sprintf('Sample%d.jpg', rand(1,3));
		$query = $this->imageRepository->createQuery();
		$result = $query->matching($query->equals('resource.filename', $randomFilename))->execute();
		$image = null;
		if (!($result && ($image = $result->getFirst()))) {
			$image = new Image($this->resourceManager->importResource(sprintf('resource://Flowpack.NodeGenerator/Private/Images/%s', $randomFilename)));
			$this->imageRepository->add($image);
			$this->persistenceManager->persistAll();
		}
		return $image;
	}

	/**
	 * @param NodeInterface $parentNode
	 * @param NodeType $nodeType
	 * @return NodeInterface The freshly created node
	 */
	abstract public function create(NodeInterface $parentNode, NodeType $nodeType);
}