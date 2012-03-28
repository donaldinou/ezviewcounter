{def $counter_ajax = 'enabled'}{if ezini_hasvariable( 'CounterAjax', 'Configuration', 'ezviewcounter.ini' )}{set $counter_ajax = ezini( 'CounterAjax', 'Configuration', 'ezviewcounter.ini' )}{/if}
{def $authorized_lib = array( 'jquery', 'yui3' )}
{if counter_ajax|eq('enabled')|not()}
{set-block scope=root variable=cache_ttl}0{/set-block}
{/if}
{if is_set($node)}
	{run-once}
		{if and( $attribute.data_int|not, has_access_to_limitation( 'ezjscore', 'call', hash( 'FunctionList', 'ezviewcounter_count' ) ))}
			{def $preferred_lib = ezini('eZJSCore', 'PreferredLibrary', 'ezjscore.ini')}{if $authorized_lib|contains( $preferred_lib )|not()}{set $preferred_lib = 'jquery'}{/if}
			{def $up = true()}{if and( is_set($update), is_boolean($update) )}{set $up = $update}{/if}
			{def $view_count = 0}{if $counter_ajax|ne('enabled')}{set $view_count = view_count($node, $up)}{/if}
			
			{ezcss_require( 'ezviewcounter.css' )}
			{if counter_ajax|eq('disabled')|not()}
			{ezscript_require( array( concat( 'ezjsc::', $preferred_lib ), concat( 'ezjsc::', $preferred_lib, 'io' ), concat( 'ezviewcounter_', $preferred_lib, '.js' ) ) )}
			{/if}
			
			<span id="ezviewcounter_{$node.node_id}_{$up}" class="counter">{$view_count}</span>
			{undef $preferred_lib $up $view_count}
		{/if}
	{/run-once}
{/if}
{undef $counter_ajax $authorized_lib}