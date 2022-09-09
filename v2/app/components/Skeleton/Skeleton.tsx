import React, { useMemo } from "react";
import styles from "./Skeleton.module.scss"
import cx from "classnames";

function stripHtml(html) {
  return html?.replace(/<[^>]*>?/gm, '');
}

function removeText(node: React.ReactNode, key?: any) {
  if (['string', 'number'].includes(typeof node)) {
    const text = (typeof node === "string") ? node : `${node}-`;
    if (!text.trim()) return null;
    return <span key={key} className={styles.loadingText}>{" "}{text.replace(/[^ ]{1,3}/g, "▬")}</span>;
  }
  if (node instanceof Array) return node.map(removeText)
  if (node && typeof node === 'object' && "props" in node) {
    if ("dangerouslySetInnerHTML" in node.props) {
      return {
        ...node,
        props: {
          ...node.props,
          dangerouslySetInnerHTML: undefined,
          children: removeText(stripHtml(node.props.dangerouslySetInnerHTML.__html))
        }
      }
    }
    if (typeof node["type"] === "function") {
      const Component = node["type"] as (props) => React.ReactNode;
      return {
        ...node,
        type: (props) => {
          return removeText(Component(props));
        }
      }
    }
    if (node["type"] === "option")
      return node;
    return {
      ...node,
      props: {
        ...node.props,
        children: removeText(node.props.children)
      }
    };
  }
  return node;
}

type Props = {
  loading: boolean;
  children?: React.ReactNode;
}
function Skeleton({ loading, children, className, ...rest }: Props & React.HTMLProps<HTMLDivElement>) {
  const transformedChildren = useMemo(() => {
    return removeText(children);
  }, []);
  return <div className={cx(styles.Skeleton, className, {
    [styles.loading]: loading
  })} {...rest}>{loading ? transformedChildren : children}</div>;
}

export default Skeleton;