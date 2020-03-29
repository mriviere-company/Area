<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* sql/relational_column_dropdown.twig */
class __TwigTemplate_b23944cdbbce9d3b396f0d3f0c5211cafb35a5268d262e44fbf372dc3d801f2f extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<span class=\"curr_value\">";
        echo twig_escape_filter($this->env, ($context["current_value"] ?? null), "html", null, true);
        echo "</span>
<a href=\"browse_foreigners.php\" data-post=\"";
        // line 2
        echo PhpMyAdmin\Url::getCommon(($context["params"] ?? null), "");
        echo "\" class=\"ajax browse_foreign\">
  ";
        // line 3
        echo _gettext("Browse foreign values");
        // line 4
        echo "</a>
";
    }

    public function getTemplateName()
    {
        return "sql/relational_column_dropdown.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  48 => 4,  46 => 3,  42 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "sql/relational_column_dropdown.twig", "/Users/EdR/Area/public/phpMyAdmin/templates/sql/relational_column_dropdown.twig");
    }
}
