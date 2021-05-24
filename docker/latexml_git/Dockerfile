FROM texmlbus_latexml_base as latexml_git

# Make a directory for latexml
RUN mkdir -p /opt/latexml

# to have revision info for compile, .git must be available
ADD .git/modules/src/LaTeXML/ /opt/latexml/.git

# latexml is installed as module, fix path
RUN sed -i '/worktree/d' /opt/latexml/.git/config


