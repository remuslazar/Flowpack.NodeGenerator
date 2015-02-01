<?php
namespace Flowpack\NodeGenerator\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.NodeGenerator".*
 *                                                                        *
 *                                                                        */

use Flowpack\NodeGenerator\Generator\NodesGenerator;
use Flowpack\NodeGenerator\Generator\PresetDefinition;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Domain\Model\Site;
use TYPO3\Neos\Domain\Service\ContentContext;
use TYPO3\TYPO3CR\Domain\Model\Node;
use TYPO3\TYPO3CR\Domain\Service\ContextInterface;

/**
 * Generator Controller
 */
class GeneratorCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\Domain\Repository\SiteRepository
	 */
	protected $siteRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository
	 */
	protected $nodeDataRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\WorkspaceRepository
	 */
	protected $workspaceRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface
	 */
	protected $contextFactory;

	/**
	 * @Flow\Inject(setting="preset")
	 * @var array
	 */
	protected $presets;

	/**
	 * Creates a big collection of node for performance benchmarking
	 * @param string $siteNode
	 * @param string $preset
	 * @param string $path
	 */
	public function nodesCommand($siteNode, $preset, $path) {
		if (!(isset($this->presets[$preset]))) {
			$this->outputLine('Error: Invalid preset');
			$this->quit(1);
		}
		$preset = $this->presets[$preset];
		/** @var Site $currentSite */
		$currentSite = $this->siteRepository->findOneByNodeName($siteNode);
		if ($currentSite === NULL) {
			$this->outputLine('Error: No site for exporting found');
			$this->quit(1);
		}
		/** @var ContentContext $contentContext */
		$contentContext = $this->createContext($currentSite, 'live');

		$workspace = 'live';
		if ($this->workspaceRepository->findByName($workspace)->count() === 0) {
			$this->outputLine('Workspace "%s" does not exist', array($workspace));
			$this->quit(1);
		}

		/** @var Node $siteNode */
		$siteNode = $contentContext->getCurrentSiteNode();

		// optionally, make the path absolute
		if ($path && strpos('/',$path) !== 0) $path = $siteNode->getPath() . '/' . $path;

		if ($path && $node = $this->nodeDataRepository->findOneByPath($path, $contentContext->getWorkspace())) {
			$siteNode = $contentContext->getNodeByIdentifier($node->getIdentifier());
		} else {
			$this->outputLine(sprintf('Error: Cannot find the node for path "%s".', $path));
			$this->quit(1);
		}

		if ($siteNode === NULL) {
			$this->outputLine('Error: No site root node');
			$this->quit(1);
		}
		$preset = new PresetDefinition($siteNode, $preset);
		$generator = new NodesGenerator($preset);

		$generator->generate();
	}

	/**
	 * @param Site $currentSite
	 * @param string $workspace
	 * @return ContextInterface
	 */
	protected function createContext(Site $currentSite, $workspace = 'live') {
		return $this->contextFactory->create(array(
			'workspaceName' => $workspace,
			'currentSite' => $currentSite,
			'invisibleContentShown' => TRUE,
			'inaccessibleContentShown' => TRUE
		));
	}
}

?>