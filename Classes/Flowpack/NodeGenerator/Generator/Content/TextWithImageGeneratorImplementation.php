<?php
namespace Flowpack\NodeGenerator\Generator\Content;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.NodeGenerator".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeType;

/**
 * Text with Images Node Generator
 */
class TextWithImageGeneratorImplementation extends TextGeneratorImplementation {

	/**
	 * @param NodeInterface $parentNode
	 * @param NodeType $nodeType
	 * @return \TYPO3\TYPO3CR\Domain\Model\NodeInterface
	 */
	public function create(NodeInterface $parentNode, NodeType $nodeType) {
		$node = parent::create($parentNode, $nodeType);
		$node->setProperty('image', $this->getRandommImageVariant());

		return $node;
	}

}