{foreach from=$options item=option}
	<dl class="{$option.object->optionName}Input">
		<dt{if $option.cssClassName} class="{$option.cssClassName}"{/if}>
			<label for="{$name}{$option.object->optionName|ucfirst}">
				{lang}{$langPrefix}.{$option.object->optionName}{/lang}
			</label>
		</dt>
		
		<dd>
			{@$option.html}
			
			{if $option.error}
				<small class="innerError">
					{if $option.error == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}{$langPrefix}.error.{$option.error}{/lang}
					{/if}
				</small>
			{/if}
			<small>{lang __optional=true}{$langPrefix}.{$option.object->optionName}.description{/lang}</small>
		</dd>
	</dl>
{/foreach}
