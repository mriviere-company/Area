<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* table/browse_foreigners/column_element.twig */
class __TwigTemplate_93a22770e1deb364eeeb5cd3d0df50b71721f0eb3c45302853ca0756ee3f0301 extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<td";
        echo ((($context["nowrap"] ?? null)) ? (" class=\"nowrap\"") : (""));
        echo ">
    ";
        // line 2
        echo ((($context["is_selected"] ?? null)) ? ("<strong>") : (""));
        echo "
        <a class=\"foreign_value\" data-key=\"";
        // line 3
        echo twig_escape_filter($this->env, ($context["keyname"] ?? null), "html", null, true);
        echo "\" href=\"#\" title=\"";
        // line 4
        echo _gettext("Use this value");
        (( !twig_test_empty(($context["title"] ?? null))) ? (print (twig_escape_filter($this->env, (": " . ($context["title"] ?? null)), "html", null, true))) : (print ("")));
        echo "\">
            ";
        // line 5
        if (($context["nowrap"] ?? null)) {
            // line 6
            echo "                ";
            echo twig_escape_filter($this->env, ($context["keyname"] ?? null), "html", null, true);
            echo "
            ";
        } else {
            // line 8
            echo "                ";
            echo twig_escape_filter($this->env, ($context["description"] ?? null), "html", null, true);
            echo "
            ";
        }
        // line 10
        echo "        </a>
    ";
        // line 11
        echo ((($context["is_selected"] ?? null)) ? ("</strong>") : (""));
        echo "
</td>
";
    }

    public function getTemplateName()
    {
        return "table/browse_foreigners/column_element.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  64 => 11,  61 => 10,  55 => 8,  49 => 6,  47 => 5,  42 => 4,  39 => 3,  35 => 2,  30 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "table/browse_foreigners/column_element.twig", "/Users/EdR/Area/public/phpMyAdmin/templates/table/browse_foreigners/column_element.twig");
    }
}
