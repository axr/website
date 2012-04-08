<div class="above_fold">
	<div class="nested_0 clearfix">
		<div class="intro">
			<h2>AXR stands for Arbitrary XML Rendering. It's aim is to provide a better alternative to HTML+CSS</h2>
			<p>It uses XML for the content and HSS for the design and simple behavior of the interface. HSS  is a language based on CSS, but offers many more advanced features, such as object orientation, rule nesting, expressions, references to other objects, modularization (code reuse), etc. JavaScript will be used as well for advanced behavior.</p>
			<a href="get_involved.html" class="join button_std">
				<span class="header"></span>
				<span class="content">
					<span class="block_0"></span>
					<span class="big">become a volunteer</span>
					<span class="small">join the revolution</span>
				</span>
				<span class="footer"></span>
			</a>
			<a href="get_involved.html" class="manifesto button_std gray">
				<span class="header"></span>
				<span class="content">
					<span class="block_0"></span>
					<span>read the manifesto</span>
				</span>
				<span class="footer"></span>
			</a>
		</div>
		<div class="slides"><iframe src="http://www.slideshare.net/slideshow/embed_code/6829398?rel=0" width="346" height="278" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe></div>
	</div>
</div>

<div class="advantages clearfix">
	<div class="nested_0">
		<h2>Advantages:</h2>
		<ul class="clearfix">
			<li class="first">
				<span class="block_0"></span>
				<span class="text">Build websites faster and easier</span>
			</li>
			<li>
				<span class="block_0"></span>
				<span class="text">Modularize and reuse your styling objects</span>
			</li>
			<li>
				<span class="block_0"></span>
				<span class="text">Vector graphics, powerful effects, textures, etc</span>
			</li>
		</ul>
		<ul class="clearfix">
    		<li class="first">
    			<span class="block_0"></span>
    			<span class="text">True semantic content</span>
    		</li>
			<li>
				<span class="block_0"></span>
				<span class="text">Exactly the same rendering across all browsers</span>
			</li>
    		<li>
    			<span class="block_0"></span>
    			<span class="text">Truly separate content from presentation</span>
    		</li>
		</ul>
		<a href="/about/features" class="learn_more button_std go">
			<span class="header"></span>
			<span class="content">Learn more<span class="block_0"></span></span>
			<span class="footer"></span>
		</a>
	</div>
</div>

<div class="hss_features">
    <div class="nested_0">
        <div class="features_menu" id="hss_features_menu">
            <h2>Features of HSS</h2>
            <div class="header"></div>
            <ul class="content">
                <li class="selected" data-section-name="hierarchical">
                    <a href="javascript:">
                        <span class="header"></span>
                        <span class="content">
                            Hierarchical
                        </span>
                        <span class="footer"></span>
                    </a>
                </li>
                <li data-section-name="object_oriented">
                    <a href="javascript:">
                        <span class="header"></span>
                        <span class="content">
                            Object oriented
                        </span>
                        <span class="footer"></span>
                    </a>
                </li>
                <li data-section-name="modular">
                    <a href="javascript:">
                        <span class="header"></span>
                        <span class="content">
                            Modular
                        </span>
                        <span class="footer"></span>
                    </a>
                </li>
                <li data-section-name="expressions">
                    <a href="javascript:">
                        <span class="header"></span>
                        <span class="content">
                            Expressions
                        </span>
                        <span class="footer"></span>
                    </a>
                </li>
                <li data-section-name="functions">
                    <a href="javascript:">
                        <span class="header"></span>
                        <span class="content">
                            Functions
                        </span>
                        <span class="footer"></span>
                    </a>
                </li>
                <li data-section-name="references">
                    <a href="javascript:">
                        <span class="header"></span>
                        <span class="content">
                            References
                        </span>
                        <span class="footer"></span>
                    </a>
                </li>
                <li data-section-name="filters">
                    <a href="javascript:">
                        <span class="header"></span>
                        <span class="content">
                            Filters
                        </span>
                        <span class="footer"></span>
                    </a>
                </li>
                <li data-section-name="structural_independence">
                    <a href="javascript:">
                        <span class="header"></span>
                        <span class="content">
                            Structural Independence
                        </span>
                        <span class="footer"></span>
                    </a>
                </li>
                <li data-section-name="layout">
                    <a href="javascript:">
                        <span class="header"></span>
                        <span class="content">
                            Layout
                        </span>
                        <span class="footer"></span>
                    </a>
                </li>
            </ul>
            <div class="footer"></div>
        </div>
        <div class="features_content" id="hss_features_content">
            <div class="selected" data-section-name="hierarchical">
                <p>Instead of writing longer and longer selector chains, the rules (the selectors + the block with the properties) can be nested inside each other. If the content in the XML file is a tree of elements, why not apply styles using a tree as well? It is both clearer and has better performance, since not all elements have to be matched against each selector.</p>
                <div class="code_frame">
                    <h3>CSS:</h3>
                    <div class="wrapper">
                        <div class="header">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                        <div class="content">
                            <div class="nested_0">
                                <div class="numbers">
                                    <span>0</span>
                                    <span>1</span>
                                    <span>2</span>
                                </div>
<code><span class="selector">books</span> { }
<span class="selector">books</span> > <span class="selector">book</span> { }
<span class="selector">books</span> > <span class="selector">book</span> > <span class="selector">cover</span> { }</code>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                    </div>
                </div>
                <div class="code_frame">
                    <h3>HSS:</h3>
                    <div class="wrapper">
                        <div class="header">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                        <div class="content">
                            <div class="nested_0">
                                <div class="numbers">
                                    <span>0</span>
                                    <span>1</span>
                                    <span>2</span>
                                    <span>3</span>
                                    <span>4</span>
                                </div>
<code><span class="selector">books</span> {
   <span class="selector">book</span> {
      <span class="selector">cover</span> { }
   }
}</code>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                    </div>
                </div>
            
            </div>
            <div data-section-name="object_oriented">
                <p>Instead of dealing with an ever growing, plain list of properties, in HSS you use objects that encapsulate a group of related values, just as objects encapsulate related functionality in traditional object oriented programming languages.</p>
                <div class="code_frame">
                    <h3>CSS:</h3>
                    <div class="wrapper">
                        <div class="header">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                        <div class="content">
                            <div class="nested_0">
                                <div class="numbers">
                                    <span>0</span>
                                    <span>1</span>
                                    <span>2</span>
                                    <span>3</span>
                                    <span>4</span>
                                    <span>5</span>
                                </div>
<code><span class="selector">selector</span>
{
   <span class="property_name">border-size</span>: <span class="numeric_value">1px</span>;
   <span class="property_name">border-color</span>: <span class="numeric_value">#F00</span>;
   <span class="property_name">border-style</span>: <span class="keyword">solid</span>;
}</code>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                    </div>
                </div>
                <div class="code_frame">
                    <h3>HSS:</h3>
                    <div class="wrapper">
                        <div class="header">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                        <div class="content">
                            <div class="nested_0">
                                <div class="numbers">
                                    <span>0</span>
                                    <span>1</span>
                                    <span>2</span>
                                    <span>3</span>
                                    <span>4</span>
                                    <span>5</span>
                                    <span>6</span>
                                </div>
<code><span class="selector">selector</span>
{
   <span class="property_name">border</span>: <span class="object_type">@lineBorder</span> {
      <span class="property_name">size</span>: <span class="numeric_value">1</span>;
      <span class="property_name">color</span>: <span class="instruction">#F00</span>;
   };
}</code>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                    </div>
                </div>

            </div>
            <div data-section-name="modular">
                <p>You can give any HSS object a name, and reuse it as needed. You can make "presets" and apply them to the selected object(s) and then even override any property as needed.</p>
                <div class="code_frame">
                    <p>For example:</p>
                    <div class="wrapper">
                        <div class="header">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                        <div class="content">
                            <div class="nested_0">
                                <div class="numbers">
                                    <span>0</span>
                                    <span>1</span>
                                    <span>2</span>
                                    <span>3</span>
                                    <span>4</span>
                                    <span>5</span>
                                    <span>6</span>
                                    <span>7</span>
                                    <span>8</span>
                                    <span>9</span>
                                    <span>10</span>
                                    <span>11</span>
                                    <span>12</span>
                                    <span>13</span>
                                    <span>14</span>
                                    <span>15</span>
                                    <span>16</span>
                                    <span>17</span>
                                </div>
<code><span class="object_type">@linearGradient</span> <span class="object_name">boxBg</span>
{
   <span class="property_name">startColor</span>: <span class="instruction">#F</span>;
   <span class="property_name">endColor</span>: <span class="instruction">#0</span>;
   <span class="property_name">endY</span>: <span class="numeric_value">100%</span>;
}

<span class="object_type">@container</span> <span class="object_name">box</span>
{
   <span class="property_name">width</span>: <span class="numeric_value">150</span>;
   <span class="property_name">height</span>: <span class="numeric_value">100</span>;
   <span class="property_name">background</span>: <span class="object_name">boxBg</span>;
}

<span class="selector">selector</span> <span class="selector">chain</span>
{
   <span class="property_name">isA</span>: <span class="object_name">box</span>;
}</code>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div data-section-name="expressions">
                <p>Anywhere a number is accepted as a value, you can also use an expression. This is especially useful when you want to mix fixed-width elements with fluid ones. For example, when you have a sidebar 150 point wide, how wide is the rest? Answer: 100% - 150.</p>
                <div class="code_frame">
                    <p>For example:</p>
                    <div class="wrapper">
                        <div class="header">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                        <div class="content">
                            <div class="nested_0">
                                <div class="numbers">
                                    <span>0</span>
                                    <span>1</span>
                                    <span>2</span>
                                    <span>3</span>
                                    <span>4</span>
                                    <span>5</span>
                                    <span>6</span>
                                    <span>7</span>
                                    <span>8</span>
                                </div>
<code><span class="selector">sidebar</span>
{
   <span class="property_name">width</span>: <span class="numeric_value">150</span>;
}

<span class="selector">content</span>
{
   <span class="property_name">width</span>: <span class="numeric_value">100%</span> - <span class="numeric_value">150</span>;
}</code>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                    </div>
                </div>
                
            </div>
            <div data-section-name="functions">
                <p>Functions like min(), max() or avg() help you control the dimensions of your elements in fluid environments, without the need for countless properties such as min-width or max-height in CSS. The function will always return the appropriate value depending on the parameters.</p>
                <div class="code_frame">
                    <p>For example:</p>
                    <div class="wrapper">
                        <div class="header">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                        <div class="content">
                            <div class="nested_0">
                                <div class="numbers">
                                    <span>0</span>
                                    <span>1</span>
                                    <span>2</span>
                                    <span>3</span>
                                    <span>4</span>
                                    <span>5</span>
                                </div>
<code><span class="selector">selector</span>
{
   <span class="comment">//make it 20% the width of the parent, but at</span>
   <span class="comment">//least 150 points wide and at most 400 points</span>
   <span class="property_name">width</span>: <span class="function_name">min</span>(<span class="numeric_value">150</span>, <span class="function_name">max</span>(<span class="numeric_value">400</span>, <span class="numeric_value">20%</span>));
}</code>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                    </div>
                </div>
                <code>
    
                </code>
            </div>
            <div data-section-name="references">
                <p>Many times it is very useful to be able to refer to a property of another element to do some calculations, for example. Imagine a site with a sidebar and content area, for example. You want to make the content area as wide as the whole page minus the sidebar, which is flexible. So you'd do something like this:</p>
                <div class="code_frame">
                    <div class="wrapper">
                        <div class="header">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                        <div class="content">
                            <div class="nested_0">
                                <div class="numbers">
                                    <span>0</span>
                                    <span>1</span>
                                    <span>2</span>
                                    <span>3</span>
                                    <span>4</span>
                                    <span>5</span>
                                    <span>6</span>
                                    <span>7</span>
                                    <span>8</span>
                                    <span>9</span>
                                    <span>10</span>
                                    <span>11</span>
                                    <span>12</span>
                                </div>
<code><span class="selector">page</span>
{
   <span class="selector">sidebar</span>
   {
      <span class="comment">//like in the previous example</span>
      <span class="property_name">width</span>: <span class="function_name">min</span>(<span class="numeric_value">150</span>, <span class="function_name">max</span>(<span class="numeric_value">400</span>, <span class="numeric_value">20%</span>));
   }

   <span class="selector">content</span>
   {
      <span class="property_name">width</span>: <span class="numeric_value">100%</span> - <span class="function_name">ref</span>(<span class="property_name">width</span> of <span class="selector">sidebar</span>);
   }
}</code>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div data-section-name="filters">
                <p>Filters are like pseudo-selectors in CSS but with a completely overhauled selection system. Since HSS has scope, the selectors are read from left to right. You select elements based on the name, for example, and then you filter that selection down to the elements you really want. There are a lot of them to cover all the different needs, so check out the docs.</p>
                <div class="code_frame">
                    <div class="wrapper">
                        <div class="header">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                        <div class="content">
                            <div class="nested_0">
                                <div class="numbers">
                                    <span>0</span>
                                    <span>1</span>
                                    <span>2</span>
                                    <span>3</span>
                                    <span>4</span>
                                    <span>5</span>
                                    <span>6</span>
                                    <span>7</span>
                                    <span>8</span>
                                    <span>9</span>
                                    <span>10</span>
                                    <span>11</span>
                                </div>
<code><span class="comment">//selects the first of the elements named foo</span>
<span class="selector">foo</span><span class="filter">:first</span> { }
<span class="comment">//selects the elements named foo that are first inside</span>
<span class="comment">//their parent</span>
<span class="selector">foo</span><span class="filter">:firstChild</span> { }
<span class="comment">//selects bar elements, then return the 2nd, 4th, 6th, etc</span>
<span class="selector">bar</span><span class="filter">:even</span> { }
<span class="comment">//selects baz elements whose width is greater than 500</span>
<span class="selector">baz</span>(<span class="property_name">width</span> &gt; <span class="numeric_value">500</span>) { }
<span class="comment">//selects qux elements whose title attribute start</span>
<span class="comment">//with "Welcome"</span>
<span class="selector">qux</span><span class="filter">:[</span><span class="selector">title</span><span class="filter">:startsWith(</span><span class="string">"Welcome"</span><span class="filter">)]</span> { }</code>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div data-section-name="structural_independence">
                <p>The structure of the content is intrinsic to its meaning, it should NEVER be altered just because of some stylistic requirements. Therefore, in HSS you can freely manipulate the content tree to fit whatever structure you need to achieve your visual layout.</p>
                <div class="code_frame">
                    <div class="wrapper">
                        <div class="header">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                        <div class="content">
                            <div class="nested_0">
                                <div class="numbers">
                                    <span>0</span>
                                    <span>1</span>
                                    <span>2</span>
                                    <span>3</span>
                                    <span>4</span>
                                    <span>5</span>
                                    <span>6</span>
                                    <span>7</span>
                                    <span>8</span>
                                    <span>9</span>
                                    <span>10</span>
                                    <span>11</span>
                                    <span>12</span>
                                    <span>13</span>
                                    <span>14</span>
                                    <span>15</span>
                                    <span>16</span>
                                    <span>17</span>
                                    <span>18</span>
                                    <span>19</span>
                                    <span>20</span>
                                    <span>21</span>
                                    <span>22</span>
                                    <span>23</span>
                                    <span>24</span>
                                    <span>25</span>
                                </div>
<code><span class="selector">root</span>
{
   <span class="comment">//wrap the element called "navigation" in a new</span>
   <span class="comment">//"header" element</span>
   <span class="instruction">#wrap</span>(<span class="selector">navigation</span>) <span class="selector">header</span>
   {
      <span class="comment">//supposing myMenuPreset has been defined elsewhere</span>
      <span class="selector">navigation</span> { <span class="property_name">isA</span>: <span class="object_name">myMenuPreset</span>; }
   }
   <span class="comment">//wrap everything that is not "header" or "footer" in a</span>
   <span class="comment">//new "content" element</span>
   <span class="instruction">#wrap</span>(!(<span class="selector">header</span>, <span class="selector">footer</span>)) <span class="selector">content</span>
   {
      <span class="comment">//change the order of the items</span>
      <span class="instruction">#move</span> <span class="selector">element2</span> { <span class="comment">/*etc*/</span> }
      <span class="instruction">#move</span> <span class="selector">element1</span> { <span class="comment">/*etc*/</span> }
      <span class="instruction">#move</span> <span class="selector">element3</span> { <span class="comment">/*etc/*</span> }
   }
   <span class="comment">//create a new footer, if not already there</span>
   <span class="instruction">#ensure</span> <span class="selector">footer</span>
   {
      <span class="comment">//create a new search box, assuming the object has been</span>
      <span class="comment">//defined elsewhere</span>
      <span class="instruction">#new</span> <span class="selector">search</span> { <span class="property_name">isA</span>: <span class="object_name">searchBox</span>; }
   }
}</code>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div data-section-name="layout">
                <p>A new box model, combined with most of the other features in this list, provide an awesome new way of doing layout, more suited to how designers think. Instead of floating, pushing and otherwise bang your head against the wall when laying out your elements on the page, you use alignX and alignY to set alignment points. These will determine the position of the elements in the page, which will flow inside of their parents, balancing themselves out when more than one tries to align itself on that specific point. It sounds more complicated than it really is, so here come some examples:</p>
                <div class="code_frame">
                    <div class="wrapper">
                        <div class="header">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                        <div class="content">
                            <div class="nested_0">
                                <div class="numbers">
                                    <span>0</span>
                                    <span>1</span>
                                    <span>2</span>
                                    <span>3</span>
                                    <span>4</span>
                                    <span>5</span>
                                    <span>6</span>
                                    <span>7</span>
                                    <span>8</span>
                                    <span>9</span>
                                    <span>10</span>
                                    <span>11</span>
                                    <span>12</span>
                                    <span>13</span>
                                    <span>14</span>
                                    <span>15</span>
                                    <span>16</span>
                                    <span>17</span>
                                    <span>18</span>
                                    <span>19</span>
                                    <span>20</span>
                                </div>
<code><span class="comment">//align foo to the right horizontally</span>
<span class="selector">foo</span> { <span class="property_name">alignX</span>: <span class="keyword">left</span>; }
<span class="comment">//align bar at the middle vertically</span>
<span class="selector">bar</span> { <span class="property_name">alignY</span>: <span class="keyword">middle</span>; }
<span class="comment">//place baz with it's right edge 30 points to the left</span>
<span class="comment">//of the center of its parent, without affecting other</span>
<span class="comment">//elements</span>
<span class="selector"></span><span class="selector">baz</span>
{
   <span class="property_name">anchorX</span>: <span class="numeric_value">100%</span>;
   <span class="property_name">alignX</span>: <span class="numeric_value">50%</span> - <span class="numeric_value">30</span>;
   <span class="property_name">flow</span>: <span class="keyword">no</span>;
}
<span class="comment">//align all elements *inside* qux at the middle vertically,</span>
<span class="comment">//and lay them out from top to bottom instead of left</span>
<span class="comment">//to right</span>
<span class="selector">qux</span>
{
   <span class="property_name">ontentAlignY</span>: <span class="keyword">middle</span>;
   <span class="property_name">directionPrimary</span>: <span class="keyword">topToBottom</span>;
}</code>
                            </div>
                        </div>
                        <div class="footer">
                            <div class="header"></div>
                            <div class="content"></div>
                            <div class="footer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="getit">
	<div class="nested_0 clearfix">
		<div class="releases">
			<h2>download</h2>
			<div class="nested_0">
				<a href="#" class="download button_big">
					<span class="header"></span>
					<span class="content">
						<span class="big">Latest version:</span>
						<span class="version">0.46 prototype</span>
						<span class="for">for OSX</span>
						<span class="block_0"></span>
					</span>
					<span class="footer"></span>
				</a>

				<div class="olds">older releases</div>
				<ul>
					<li>
						<span class="version">v 0.45</span>
						<span class="date">2012/03/21</span>
						<a class="download" href="#">Download</a>
					</li>
					<li>
						<span class="version">v 0.441</span>
						<span class="date">2012/02/15</span>
						<a class="download" href="#">Download</a>
					</li>
					<li>
						<span class="version">v 0.44</span>
						<span class="date">2012/01/01</span>
						<a class="download" href="#">Download</a>
					</li>
				</ul>
			</div>
			<a class="altos" href="/resources/downloads">need other operating system?</a>
			<div class="block_0"></div>
			<div class="block_1"></div>
			<div class="block_2"></div>
			<div class="block_3"></div>
		</div>
		<div class="changes">
			<h2>Latest changes:</h2>
			<div class="verinfo">
				<span class="version">v 0.46</span>
				Released: 2012/04/02
			</div>
			<ul>
				<li>
					<span class="block_0"></span>
					<span class="text">Fix toggleFlag()</span>
				</li>
				<li>
					<span class="block_0"></span>
					<span class="text">Add @click event (as a clone of mouseUp, for now)</span>
				</li>
				<li>
					<span class="block_0"></span>
					<span class="text">Various small fixes</span>
				</li>
				<li>
					<span class="block_0"></span>
					<span class="text">Add tree change notification</span>
				</li>
				<li>
					<span class="block_0"></span>
					<span class="text">Add tree change notification and some other stuff that will make this line very long</span>
				</li>
				<li>
					<span class="block_0"></span>
					<span class="text">Various small fixes</span>
				</li>
				<li class="clear"></li>
			</ul>

			<a href="#" class="see_all button_std go">
				<span class="header"></span>
				<span class="content"><span class="block_0"></span>See all</span>
				<span class="footer"></span>
			</a>
		</div>
	</div>
</div>

<!--<div class="the_cool_stuff">
	<div class="nested_0">
		<div class="downloads">
			<div class="download buttons">
				<a class="latest" href="prototype.dmg"><span class="label">download</span><span class="version">0.42 prototype</span></a>
				<a class="older" href="older_versions.html">older versions</a>
			</div>
			<div class="download info">
				<h2>System Requirements</h2>
				<p>Right now the prototype requires Mac OS X 10.6 or newer, on a 64bit Intel Mac. Support for Windows and Linux will come soon.</p>
			</div>
		</div>
		<div class="advantages">
			<h2>Advantages:</h2>
			<ul>
				<li><span class="block_0"></span><span>Build websites faster and easier</span></li>
				<li><span class="block_0"></span><span>Separate content from presentation</span></li>
				<li><span class="block_0"></span><span>Modularize and reuse your styling objects</span></li>
				<li><span class="block_0"></span><span>Exactly the same rendering across all browsers</span></li>
				<li><span class="block_0"></span><span>Vector graphics, powerful effects, textures, etc</span></li>
				<li><span class="block_0"></span><span>True semantic content</span></li>
			</ul>
			<a href="features.html" class="features button_std">
				<span class="header"></span>
				<span class="content">See all features<span class="block_0"></span></span>
				<span class="footer"></span>
			</a>
		</div>
	</div>
</div>-->

