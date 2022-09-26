% Use your own class of Latex document here
\documentclass{article}
\usepackage[latin1]{inputenc}
\usepackage[T1]{fontenc}
\usepackage{multicol}
\usepackage[english]{babel}
\usepackage[final]{pdfpages}
\usepackage[margin=2cm,includefoot,includehead]{geometry}
\usepackage{makeidx}
\usepackage{index}
\usepackage[bookmarks=yes]{hyperref}

\makeindex

% Declare the index of authors. Note: the index must
% be produced with the following command (after a first Latex compilation):
% makeindex booklet.ax -o booklet.ad
\newindex{authors}{ax}{ad}{Index of authors}

% Some pdfpages parameters
\includepdfset{pages=-,pagecommand={}}

% OK, here begins the document
\begin{document}

% The title page
\title{ \textbf{{CONF_NAME} \newline Booklet of abstracts}}
\author{}
\date{\empty}

\maketitle

% The table of contents
\tableofcontents

% The program committee
\newpage
\section*{Program committee}
\addcontentsline{toc}{section}{Program committee}
\begin{multicols}{2}
\input{pc}
\end{multicols}

\newpage
\addcontentsline{toc}{section}{Program}
\section*{Program}
\bigskip
\input{program}

\newpage
\addcontentsline{toc}{section}{List of abstracts}
\section*{List of abstracts}
\input{abstracts}

\addcontentsline{toc}{section}{Index of authors}
\printindex[authors]

\end{document}
