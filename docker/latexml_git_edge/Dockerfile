FROM texmlbus_latexml_base_edge as latexml_git_edge

# Make a directory for latexml
RUN mkdir -p /opt/latexml

##
## LaTeXML_edge is added as normal repository, not as submodule. 
## Therefore copy from that directory.
# to have revision info for compile, .git must be available
#ADD .git/modules/src/LaTeXML_edge/ /opt/latexml/.git

# latexml is installed as module, fix path
#RUN sed -i '/worktree/d' /opt/latexml/.git/config

ADD docker/LaTeXML_edge/.git /opt/latexml/.git



