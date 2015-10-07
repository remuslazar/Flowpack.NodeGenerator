<?php
namespace Flowpack\NodeGenerator\Generator;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.NodeGenerator".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\ConsoleOutput;
use TYPO3\Flow\Exception;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeType;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;
use TYPO3\TYPO3CR\Exception\NodeExistsException;

/**
 * Node Generator
 */
class NodesGenerator {

	/**
	 * @Flow\Inject
	 * @var NodeTypeManager
	 */
	protected $nodeTypeManager;

	/**
	 * @Flow\Inject
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var PresetDefinition
	 */
	protected $preset;

	/**
	 * @Flow\Inject(setting="generator")
	 * @var array
	 */
	protected $generators;

	/**
	 * @var ConsoleOutput
	 */
	protected $consoleOutput;

	/**
	 * @param PresetDefinition $preset
	 */
	function __construct(PresetDefinition $preset, ConsoleOutput $consoleOutput) {
		$this->preset = $preset;
		$this->consoleOutput = $consoleOutput;
	}

	public function generate() {
		$siteNode = $this->preset->getSiteNode();
		$this->createBatchDocumentNode($siteNode);
	}

	/**
	 * @param NodeType $nodeType
	 * @return NodeGeneratorImplementationInterface
	 * @throws \TYPO3\Flow\Exception
	 */
	protected function getNodeGeneratorImplementationClassByNodeType(NodeType $nodeType) {
		if (!isset($this->generators[(string)$nodeType]['class'])) {
			throw new Exception(sprintf('Unknown generator for the current Node Type (%s)', (string)$nodeType, 1391771111));
		}
		return $this->objectManager->get($this->generators[(string)$nodeType]['class']);
	}

	/**
	 * @param NodeInterface $baseNode
	 * @param int $level
	 */
	protected function createBatchDocumentNode(NodeInterface $baseNode, $level = 0) {

		$maxNodeByLevel = $this->preset->getNodeByLevel();

		if ($level == 0 && $this->consoleOutput) $this->consoleOutput->progressStart($maxNodeByLevel);

		for ($i = 0; $i < $maxNodeByLevel; $i++) {
			if ($level == 0 && $this->consoleOutput) $this->consoleOutput->progressAdvance();
			try {
				$nodeType = $this->nodeTypeManager->getNodeType($this->preset->getDocumentNodeType());
				$generator = $this->getNodeGeneratorImplementationClassByNodeType($nodeType);
				$childrenNode = $generator->create($baseNode, $nodeType);
				$this->createBatchContentNodes($childrenNode);
				if ($level < $this->preset->getDepth()) {
					$level++;
					$this->createBatchDocumentNode($childrenNode, $level);
				}
			} catch (NodeExistsException $e) {

			}
		}

		if ($level == 0 && $this->consoleOutput) $this->consoleOutput->progressFinish();
	}

	/**
	 * @param NodeInterface $documentNode
	 */
	protected function createBatchContentNodes(NodeInterface $documentNode) {
		$mainContentCollection = $documentNode->getNode('main');
		for ($j = 0; $j < $this->preset->getContentNodeByDocument(); $j++) {
			try {
				$nodeType = $this->nodeTypeManager->getNodeType($this->preset->getContentNodeType());
				$generator = $this->getNodeGeneratorImplementationClassByNodeType($nodeType);
				$generator->create($mainContentCollection, $nodeType);
			} catch (NodeExistsException $e) {

			}
		}
	}

}

?>