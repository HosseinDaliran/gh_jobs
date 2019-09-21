<?php

namespace Drupal\gh_jobs_migrate\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "gh_jobs_migrate_block",
 *   admin_label = @Translation("Greenhouse Job Apply Form"),
 * )
 */
class GreenhouseApplyBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal RouteMatch instance.
   *
   * @var Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * Getting the node id from the route.
   *
   * @return mixed
   *   Null or the parameter value.
   */
  protected function getNode() {
    $obj = $this->routeMatch->getParameter('node');

    if (!$obj instanceof NodeInterface) {
      throw new \UnexpectedValueException("Not a node page");
    }

    if ($obj->bundle() !== 'job') {
      throw new \UnexpectedValueException("Not an Job node");
    }

    return $obj;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $currentJob = $this->getNode();

    return [
      '#theme' => 'gh_jobs_migrate__apply_block',
      '#attached' => [
        'library' => [
          'gh_jobs/embed_gh_board_scrip' => 'gh_jobs/embed_gh_board_scrip',
        ],
        'drupalSettings' => [
          'GHJobs' => [
            'jid' => $currentJob->get('field_gh_id')->value,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    try {
      $this->getNode();
    }
    catch (\UnexpectedValueException $ex) {
      return AccessResult::forbidden();
    }

    return parent::blockAccess($account);
  }

}
